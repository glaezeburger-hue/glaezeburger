<!-- Global Bluetooth Printer Dispatcher -->
<div x-data="globalPrinterApp()" 
     @print-receipt.window="printGlobalReceipt($event.detail)"
     class="relative z-[150000]">

    <!-- Web Bluetooth ESC/POS Service (Loaded Once) -->
    @once
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    @endonce

    <script>
        function globalPrinterApp() {
            return {
                printer: null,
                isPrintingReceipt: false,
                printProgress: 0,
                
                async printGlobalReceipt(transactionId) {
                    if (!this.printer) {
                        this.printer = new BluetoothPrinter();
                    }
                    
                    // Always try auto-connect first if not explicitly connected
                    if (!this.printer.device || !this.printer.device.gatt.connected) {
                        const res = await this.printer.autoConnect();
                        if (!res.success) {
                            // Prompt to scan a new printer
                            const result = await Swal.fire({
                                title: 'Printer Terputus',
                                text: 'Tidak ada riwayat printer yang terhubung (atau printer di luar jangkauan). Anda ingin memindai printer baru sekarang?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#0A56C8',
                                cancelButtonColor: '#64748b',
                                confirmButtonText: 'Scan Printer',
                                cancelButtonText: 'Batal',
                                reverseButtons: true,
                                customClass: {
                                    popup: 'rounded-[2rem]',
                                    confirmButton: 'rounded-xl px-6 py-3 uppercase tracking-widest font-black text-[10px]',
                                    cancelButton: 'rounded-xl px-6 py-3 uppercase tracking-widest font-black text-[10px]'
                                }
                            });

                            if (result.isConfirmed) {
                                const connectRes = await this.printer.connect();
                                if (!connectRes.success) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Koneksi Gagal',
                                        text: connectRes.error,
                                        customClass: { popup: 'rounded-[2rem]' }
                                    });
                                    return;
                                }
                            } else {
                                return; // Batal mencetak
                            }
                        }
                    }

                    // Proceed to print
                    this.isPrintingReceipt = true;
                    this.printProgress = 0;
                    
                    try {
                        const response = await fetch(`{{ url('/transactions') }}/${transactionId}/receipt-data`);
                        if (!response.ok) throw new Error('Data struk tidak ditemukan');
                        const data = await response.json();
                        
                        // Apply logo from localStorage if any, otherwise fetch and cache it now
                        let logoB64 = localStorage.getItem('glaeze_logo_base64');
                        if (!logoB64) {
                            try {
                                const logoRes = await fetch('{{ asset('images/logo.png') }}');
                                const logoBlob = await logoRes.blob();
                                logoB64 = await new Promise((resolve) => {
                                    const reader = new FileReader();
                                    reader.onloadend = () => resolve(reader.result);
                                    reader.readAsDataURL(logoBlob);
                                });
                                localStorage.setItem('glaeze_logo_base64', logoB64);
                            } catch (error) {
                                console.warn("Failed to load global logo for printing:", error);
                            }
                        }
                        
                        data.logo_url = logoB64;
                        
                        await this.printer.printReceipt(data, (percent) => {
                            this.printProgress = percent;
                            if (percent >= 100) {
                                setTimeout(() => this.isPrintingReceipt = false, 1000);
                            }
                        });
                    } catch (e) {
                        console.error(e);
                        this.isPrintingReceipt = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Pencetakan Gagal',
                            text: e.message,
                            customClass: { popup: 'rounded-[2rem]' }
                        });
                    }
                }
            }
        }
    </script>

    <!-- Printing Progress Overlay -->
    <div x-show="isPrintingReceipt" style="display: none;" class="fixed inset-0 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-80 p-6 flex flex-col items-center text-center transform scale-100 animate-in zoom-in-95 duration-200">
            <div class="relative w-16 h-16 mb-4 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
                <!-- Semi-circle spinner -->
                <svg class="absolute inset-0 w-full h-full text-blue-600 animate-spin" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="46" fill="transparent" stroke="currentColor" stroke-width="8" stroke-dasharray="200" stroke-linecap="round"></circle>
                </svg>
                <i class="fas fa-print text-xl text-blue-600"></i>
            </div>
            
            <h3 class="text-lg font-bold text-gray-800 mb-1">Mencetak Struk...</h3>
            <p class="text-sm text-gray-500 font-medium mb-5">Harap tunggu, mengirim data ke printer</p>
            
            <!-- Progress Bar inside overlay -->
            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2 overflow-hidden shadow-inner flex justify-start">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 ease-out relative" :style="{ width: printProgress + '%' }">
                    <!-- Shimmer effect -->
                    <div class="absolute top-0 inset-x-0 h-full bg-white/30 animate-pulse"></div>
                </div>
            </div>
            
            <div class="flex justify-between w-full text-xs font-bold text-gray-400">
                <span>Sending payload</span>
                <span x-text="printProgress + '%'" class="text-blue-600">0%</span>
            </div>
        </div>
    </div>
</div>
