/**
 * ESC/POS Bluetooth Printer Service for VSC MP-58A
 * 58mm Thermal Printer (32 Characters per line)
 */

class BluetoothPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.characteristic = null;
        this.isPrinting = false;
        
        // Common UUIDs for thermal printers
        this.services = ['000018f0-0000-1000-8000-00805f9b34fb', 'e7810a71-73ae-499d-8c15-faa9aef0c3f2'];
        this.characteristics = ['00002af1-0000-1000-8000-00805f9b34fb', 'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f'];
    }

    async connect() {
        try {
            if (!navigator.bluetooth) {
                throw new Error("Web Bluetooth API is not available. Please use Chrome on HTTPS/localhost.");
            }

            this.device = await navigator.bluetooth.requestDevice({
                filters: [
                    { services: this.services },
                    { namePrefix: 'MP' }, // VSC MP-58A usually starts with MP or similar
                    { namePrefix: 'MPT' },
                    { namePrefix: 'VSC' },
                    { namePrefix: 'Bluetooth Printer' }
                ],
                optionalServices: this.services
            });

            this.device.addEventListener('gattserverdisconnected', this.onDisconnected.bind(this));

            this.server = await this.device.gatt.connect();
            
            // Find working service and characteristic
            let serviceFound = null;
            for (const uuid of this.services) {
                try {
                    serviceFound = await this.server.getPrimaryService(uuid);
                    break;
                } catch (e) {
                    console.log(`Service ${uuid} not found, trying next...`);
                }
            }

            if (!serviceFound) throw new Error("Could not find printer service");

            let charFound = null;
            for (const uuid of this.characteristics) {
                try {
                    charFound = await serviceFound.getCharacteristic(uuid);
                    break;
                } catch (e) {
                    console.log(`Characteristic ${uuid} not found, trying next...`);
                }
            }

            if (!charFound) throw new Error("Could not find printer characteristic");

            this.characteristic = charFound;
            
            // Save device info for auto-reconnect attempt next time
            localStorage.setItem('lastPrinterId', this.device.id);
            localStorage.setItem('lastPrinterName', this.device.name || 'Printer');

            return { success: true, name: this.device.name || 'Bluetooth Printer' };

        } catch (error) {
            console.error("Bluetooth Connect Error:", error);
            this.device = null;
            this.server = null;
            this.characteristic = null;
            return { success: false, error: error.message };
        }
    }

    async autoConnect(specificDeviceId = null) {
        try {
            if (!navigator.bluetooth || !navigator.bluetooth.getDevices) {
                return { success: false, error: "Auto-reconnect not supported in this browser" };
            }

            const devices = await navigator.bluetooth.getDevices();
            if (devices.length === 0) {
                return { success: false, error: "No previously paired printers found" };
            }

            let targetDevice = null;
            
            if (specificDeviceId) {
                targetDevice = devices.find(d => d.id === specificDeviceId);
            } else {
                const lastId = localStorage.getItem('lastPrinterId');
                targetDevice = devices.find(d => d.id === lastId) || devices[0];
            }
            
            if (!targetDevice) {
                 return { success: false, error: "Selected printer not found in paired history" };
            }

            this.device = targetDevice;
            this.device.addEventListener('gattserverdisconnected', this.onDisconnected.bind(this));

            // Optional: try waking up the device by watching advertisements
            try {
                if (this.device.watchAdvertisements) {
                    await this.device.watchAdvertisements();
                }
            } catch (e) {
                console.log("watchAdvertisements not supported/permitted:", e);
            }

            // Retry connection up to 3 times (crucial for cached devices)
            let connected = false;
            let lastError = null;
            for (let i = 0; i < 3; i++) {
                try {
                    console.log(`Attempting GATT connect (Attempt ${i + 1})...`);
                    this.server = await this.device.gatt.connect();
                    connected = true;
                    break;
                } catch (e) {
                    lastError = e;
                    console.warn(`GATT Connect attempt ${i + 1} failed: `, e);
                    await new Promise(res => setTimeout(res, 1000)); // wait 1s before retry
                }
            }
            
            if (!connected || !this.server) {
                throw new Error(lastError ? lastError.message : "GATT Connection failed after multiple attempts. Is the printer ON nearby?");
            }
            
            // Find working service and characteristic
            let serviceFound = null;
            for (const uuid of this.services) {
                try {
                    serviceFound = await this.server.getPrimaryService(uuid);
                    break;
                } catch (e) {}
            }
            if (!serviceFound) throw new Error("Could not find printer service (Make sure the printer is turned ON)");

            let charFound = null;
            for (const uuid of this.characteristics) {
                try {
                    charFound = await serviceFound.getCharacteristic(uuid);
                    break;
                } catch (e) {}
            }
            if (!charFound) throw new Error("Could not find printer characteristic");

            this.characteristic = charFound;
            localStorage.setItem('lastPrinterId', this.device.id);
            localStorage.setItem('lastPrinterName', this.device.name || 'Printer');

            return { success: true, name: this.device.name || 'Bluetooth Printer' };

        } catch (error) {
            console.error("Auto-Connect Error:", error);
            this.device = null;
            this.server = null;
            this.characteristic = null;
            return { success: false, error: error.message };
        }
    }

    onDisconnected() {
        console.log("Printer disconnected");
        this.server = null;
        this.characteristic = null;
        // Optionally trigger a custom event that Alpine can listen to
        window.dispatchEvent(new CustomEvent('printer-disconnected'));
    }

    async disconnect() {
        if (this.device && this.device.gatt.connected) {
            this.device.gatt.disconnect();
        }
    }

    formatLine(left, right) {
        const LINE_WIDTH = 32;
        // Ensure strings
        left = String(left);
        right = String(right);

        // If even without space it exceeds, we might need to truncate left
        if (left.length + right.length >= LINE_WIDTH) {
            left = left.substring(0, LINE_WIDTH - right.length - 1);
        }

        const spaceLength = LINE_WIDTH - left.length - right.length;
        return left + ' '.repeat(Math.max(1, spaceLength)) + right;
    }

    /**
     * Converts an HTML Image Element into ESC/POS GS v 0 Bit Image array.
     * Uses Canvas for resizing and 1-bit thresholding.
     */
    async imageToEscPos(imgUrl, maxWidth = 200) {
        return new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = "Anonymous";
            img.onload = () => {
                const canvas = document.createElement('canvas');
                // Ensure width is multiple of 8 for easy bit-packing
                let targetWidth = Math.min(img.width, maxWidth);
                targetWidth = Math.ceil(targetWidth / 8) * 8; 
                
                const scale = targetWidth / img.width;
                const targetHeight = Math.round(img.height * scale);
                
                canvas.width = targetWidth;
                canvas.height = targetHeight;
                
                const ctx = canvas.getContext('2d');
                // Fill white background (crucial for transparent PNGs)
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                const widthBytes = canvas.width / 8;
                
                // GS v 0 command structure
                const ESCPOS_IMAGE_CMD = [0x1D, 0x76, 0x30, 0];
                const xL = widthBytes & 0xFF;
                const xH = (widthBytes >> 8) & 0xFF;
                const yL = canvas.height & 0xFF;
                const yH = (canvas.height >> 8) & 0xFF;

                let escposData = [...ESCPOS_IMAGE_CMD, xL, xH, yL, yH];
                
                // Bit-packing threshold algorithm
                for (let y = 0; y < canvas.height; y++) {
                    for (let x = 0; x < canvas.width; x += 8) {
                        let byte = 0;
                        for (let b = 0; b < 8; b++) {
                            const pixelX = x + b;
                            const i = (y * canvas.width + pixelX) * 4;
                            const r = imgData[i];
                            const g = imgData[i+1];
                            const bVal = imgData[i+2];
                            const a = imgData[i+3];
                            
                            // Luminance formula
                            const luminance = (0.299 * r + 0.587 * g + 0.114 * bVal);
                            
                            // If dark enough and not transparent, it's a black dot (1)
                            if (a > 128 && luminance < 128) {
                                byte |= (1 << (7 - b));
                            }
                        }
                        escposData.push(byte);
                    }
                }
                resolve(escposData);
            };
            img.onerror = () => {
                console.warn("Failed to load logo image");
                resolve(null);
            };
            img.src = imgUrl;    
        });
    }

    async printReceipt(data, onProgress = null) {
        if (!this.characteristic) {
            throw new Error("Printer is not connected");
        }
        
        if (this.isPrinting) {
            throw new Error("Printer is currently busy");
        }

        this.isPrinting = true;

        try {
            const encoder = new TextEncoder();
            let receipt = [];

            // Helper to add bytes
            const add = (bytes) => {
                if (typeof bytes === 'string') {
                    receipt.push(...encoder.encode(bytes));
                } else {
                    receipt.push(...bytes);
                }
            };

            // ESC/POS Commands
            const ESC = 0x1B;
            const GS = 0x1D;
            const INIT = [ESC, 0x40];
            const ALIGN_LEFT = [ESC, 0x61, 0x00];
            const ALIGN_CENTER = [ESC, 0x61, 0x01];
            const BOLD_ON = [ESC, 0x45, 1];
            const BOLD_OFF = [ESC, 0x45, 0];
            const DOUBLE_H = [GS, 0x21, 0x01];  // Double height only
            const DOUBLE_W = [GS, 0x21, 0x10];  // Double width only (16 chars/line)
            const DOUBLE_HW = [GS, 0x21, 0x11]; // Double height & width (16 chars/line)
            const NORMAL_SIZE = [GS, 0x21, 0x00];
            const UNDERLINE_ON = [ESC, 0x2D, 1];
            const UNDERLINE_OFF = [ESC, 0x2D, 0];

            // Separator styles
            const LINE_DASH = "--------------------------------\n";  // 32 dashes
            const LINE_EQUAL = "================================\n"; // 32 equals
            const LINE_DOT = "................................\n";   // 32 dots

            // Format number helpers
            const fmtRp = (num) => {
                return new Intl.NumberFormat('id-ID').format(Math.round(num));
            };

            // ─── 1. INITIALIZE ───
            add(INIT);
            add(ALIGN_CENTER);

            // ─── 2. HEADER / BRAND LOGO ───
            if (data.logo_url) {
                // Try to load and convert the image
                const logoBytes = await this.imageToEscPos(data.logo_url, 200);
                if (logoBytes && logoBytes.length > 0) {
                    add(logoBytes);
                    add("Street Smash Burger\n");
                    add("Centra Niaga Square\n");
                    add("Cikarang Utara, Kab Bekasi\n");
                }
            } else {
                // Fallback Text Header if no logo URL provided or failed
                add(BOLD_ON);
                add(DOUBLE_HW);
                add("GLAEZE\n");
                add(NORMAL_SIZE);
                add(DOUBLE_H);
                add("BURGER\n");
                add(NORMAL_SIZE);
                add(BOLD_OFF);
                add("\n");
                add("Street Smash Burger\n");
                add("Centra Niaga Square\n");
                add("Cikarang Utara, Kab Bekasi\n");
            }
            add(LINE_EQUAL);

            // ─── 3. TRANSACTION INFO ───
            add(ALIGN_LEFT);
            // Extract short order number from invoice
            const orderNum = data.invoice_number || '-';
            add(this.formatLine("No.", orderNum) + "\n");
            add(this.formatLine("Tanggal", data.created_at || '-') + "\n");
            add(this.formatLine("Kasir", data.cashier || '-') + "\n");
            add(this.formatLine("Bayar", (data.payment_method || 'Cash').toUpperCase()) + "\n");
            add(LINE_DASH);

            // ─── 4. ORDER ITEMS ───
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    let name = item.product_name || 'Item';
                    
                    // Print item name (bold)
                    add(BOLD_ON);
                    if (name.length > 32) {
                        add(name.substring(0, 32) + "\n");
                        add(name.substring(32, 64) + "\n");
                    } else {
                        add(name + "\n");
                    }
                    add(BOLD_OFF);

                    // Print Variations if any
                    if (item.variations && item.variations.length > 0) {
                        item.variations.forEach(v => {
                            let varLine = `  + ${v.option}`;
                            if (v.price_modifier > 0) {
                                let modStr = `+${fmtRp(v.price_modifier)}`;
                                add(this.formatLine(varLine, modStr) + "\n");
                            } else {
                                add(varLine + "\n");
                            }
                        });
                    }

                    // Print notes if any
                    if (item.notes) {
                        add(`  * ${item.notes}\n`);
                    }

                    // Print qty x price = subtotal
                    const qtyPrice = `  ${item.quantity}x ${fmtRp(item.price)}`;
                    const subtotal = fmtRp(item.subtotal);
                    add(this.formatLine(qtyPrice, subtotal) + "\n");
                });
            }
 
            add(LINE_DASH);

            // ─── 5. SUBTOTAL & ADJUSTMENTS ───
            add(this.formatLine("Subtotal", fmtRp(data.subtotal)) + "\n");

            if (parseFloat(data.discount_amount) > 0) {
                add(this.formatLine("Diskon", "-" + fmtRp(data.discount_amount)) + "\n");
            }

            if (parseFloat(data.voucher_discount_amount) > 0) {
                let vLabel = "Voucher";
                if (data.voucher_code) vLabel += ` (${data.voucher_code})`;
                // Truncate voucher label if too long
                if (vLabel.length > 20) vLabel = vLabel.substring(0, 20);
                add(this.formatLine(vLabel, "-" + fmtRp(data.voucher_discount_amount)) + "\n");
            }

            if (parseFloat(data.tax_amount) > 0) {
                add(this.formatLine("PB1 (10%)", fmtRp(data.tax_amount)) + "\n");
            }

            // ─── 6. GRAND TOTAL ───
            add(LINE_EQUAL);
            add(BOLD_ON);
            add(this.formatLine("TOTAL", "Rp " + fmtRp(data.total_amount)) + "\n");
            add(BOLD_OFF);
            add(LINE_EQUAL);

            // ─── 7. PAYMENT DETAILS ───
            const payMethod = (data.payment_method || 'Cash').toUpperCase();
            
            if (payMethod === 'CASH') {
                const cashReceived = parseFloat(data.cash_received) || 0;
                const changeAmount = parseFloat(data.change_amount) || 0;
                
                if (cashReceived > 0) {
                    add(this.formatLine("Tunai", "Rp " + fmtRp(cashReceived)) + "\n");
                    add(this.formatLine("Kembali", "Rp " + fmtRp(changeAmount)) + "\n");
                }
            } else if (payMethod === 'QRIS') {
                add(this.formatLine("Metode", "QRIS") + "\n");
                add(this.formatLine("Status", "LUNAS") + "\n");
            }

            add("\n");

            // ─── 8. FOOTER ───
            add(ALIGN_CENTER);
            add(LINE_DOT);
            add(BOLD_ON);
            add("THANK YOU FOR VISITING\n");
            add(BOLD_OFF);
            add("Follow & Tag Us\n");
            add("IG & Tiktok: @glaezeburger\n");
            add(LINE_DOT);

            // ─── 9. ORDER COUNT / ITEM COUNT ───
            const totalItems = data.items ? data.items.reduce((sum, i) => sum + i.quantity, 0) : 0;
            add(`Total ${totalItems} item(s)\n`);

            // ─── 10. FEED FOR MANUAL TEAR ───
            add("\n\n\n\n");

            // Convert array to Uint8Array and send in chunks
            const dataBuffer = new Uint8Array(receipt);
            const CHUNK_SIZE = 256; // Reduced to 256 bytes for safer BLE transmission of images
            const totalBytes = dataBuffer.length;
            
            for (let i = 0; i < totalBytes; i += CHUNK_SIZE) {
                const chunk = dataBuffer.slice(i, i + CHUNK_SIZE);
                await this.characteristic.writeValue(chunk);
                
                if (onProgress) {
                    const percent = Math.min(100, Math.round(((i + chunk.length) / totalBytes) * 100));
                    onProgress(percent);
                }
                
                // Allow hardware buffer to catch up
                await new Promise(resolve => setTimeout(resolve, 30));
            }

            return { success: true };

        } catch (error) {
            console.error("Print Error:", error);
            return { success: false, error: error.message };
        } finally {
            this.isPrinting = false;
        }
    }
}

window.BluetoothPrinter = BluetoothPrinter;
