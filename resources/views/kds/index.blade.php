<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display — GLÆZE Burger</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] font-sans antialiased overflow-hidden">
    <div x-data="{ sidebarOpen: false, ...kdsApp() }" class="h-screen flex flex-col bg-[#F8FAFC] relative">
        
        <!-- KDS Header — mirrors POS header pattern -->
        <header class="bg-white p-6 border-b border-gray-100 shadow-sm shrink-0 relative z-30">
            <div class="flex items-center justify-between">
                <!-- Left: Back + Branding -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="p-2.5 rounded-xl bg-gray-50 text-gray-400 hover:text-smash-blue hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 tracking-tighter uppercase leading-none">Kitchen Display</h1>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">GLÆZE BURGER KDS System</p>
                    </div>
                </div>

                <!-- Center: Queue Status -->
                <div class="flex items-center space-x-3 bg-gray-50 px-5 py-2.5 rounded-2xl border border-gray-100">
                    <span x-text="orders.length" class="text-smash-blue text-xl font-black leading-none tabular-nums">0</span>
                    <span class="text-gray-400 font-black text-[10px] uppercase tracking-widest" x-text="orders.length === 1 ? 'Active Order' : 'Active Orders'"></span>
                </div>

                <!-- Right: Clock + Status -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div x-text="currentTime" class="text-gray-900 font-black text-2xl tracking-tight leading-none tabular-nums">00:00:00</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Station Time</div>
                    </div>
                    <div class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center bg-gray-50 relative">
                        <div class="w-2.5 h-2.5 bg-green-500 rounded-full"></div>
                        <div class="absolute inset-0 w-full h-full bg-green-500 rounded-full animate-ping opacity-20"></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- KDS Grid Area -->
        <div class="flex-1 overflow-y-auto p-8">
            <!-- Empty State -->
            <template x-if="orders.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-center select-none">
                    <div class="w-32 h-32 bg-white rounded-3xl border border-gray-100 shadow-lg flex items-center justify-center mb-8">
                        <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">Kitchen Clear</h2>
                    <p class="text-sm font-bold text-gray-400 mt-3 uppercase tracking-widest">Waiting for new orders</p>
                </div>
            </template>

            <!-- Order Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8">
                <template x-for="order in orders" :key="order.id">
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col border-t-4"
                         :class="{
                            'border-red-500': isOverdue(order.created_at) && order.payment_status === 'Paid',
                            'border-smash-blue': !isOverdue(order.created_at) && order.payment_status === 'Paid',
                            'border-orange-400 border-dashed opacity-80': order.payment_status === 'Pending'
                         }">
                        
                        <!-- Card Header -->
                        <div class="px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-3">
                                <template x-if="order.payment_status === 'Pending'">
                                    <div class="px-2 py-0.5 bg-orange-50 text-orange-500 rounded-lg border border-orange-100 text-[8px] font-black uppercase tracking-[0.15em] flex items-center shrink-0">
                                        <div class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-1.5 opacity-75 animate-pulse"></div>
                                        WAITING PAYMENT
                                    </div>
                                </template>
                                <template x-if="order.payment_status === 'Paid'">
                                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                                        <span x-text="isOverdue(order.created_at) ? 'PRIORITY' : 'IN PROGRESS'"></span>
                                    </div>
                                </template>

                                <div class="text-right">
                                    <span class="text-[9px] font-bold uppercase tracking-widest block"
                                          :class="isOverdue(order.created_at) ? 'text-red-500' : 'text-gray-400'">Elapsed</span>
                                </div>
                            </div>
                            
                            <div class="flex items-end justify-between">
                                <div class="min-w-0">
                                    <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest block mb-0.5">Invoice</span>
                                    <h3 class="text-base font-black text-gray-900 tracking-tight truncate uppercase" x-text="order.invoice_number"></h3>
                                    <template x-if="order.customer_name">
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <div class="px-2 py-0.5 bg-blue-50 text-smash-blue rounded-md border border-blue-100 text-[11px] font-black uppercase tracking-widest inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                <span x-text="order.customer_name"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="text-2xl font-black tracking-tighter tabular-nums leading-none ml-4 shrink-0"
                                     :class="isOverdue(order.created_at) ? 'text-red-600' : 'text-gray-900'"
                                     x-text="getElapsedTime(order.created_at)"></div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="mx-6 border-b border-gray-100"></div>

                        <!-- Card Body (Items) -->
                        <div class="flex-1 px-6 py-5 space-y-3">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="bg-gray-50 px-4 py-3 rounded-xl border border-gray-100">
                                    <div class="flex items-center">
                                        <div class="bg-smash-blue text-white w-10 h-10 rounded-xl text-xs font-black flex items-center justify-center mr-3 shrink-0 shadow-sm" x-text="item.quantity + 'x'"></div>
                                        <div class="text-[14px] font-black text-gray-900 uppercase leading-none tracking-tight" x-text="item.name"></div>
                                    </div>
                                    
                                    <!-- Variations -->
                                    <template x-if="item.variations && item.variations.length > 0">
                                        <div class="mt-2 flex flex-wrap gap-1 pl-13 ml-[52px]">
                                            <template x-for="v in item.variations" :key="v.name">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
                                                      :class="parseFloat(v.price_modifier) < 0 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'">
                                                    <span x-text="parseFloat(v.price_modifier) < 0 ? '-' : '+'" class="mr-1"></span> <span x-text="v.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Addons -->
                                    <template x-if="item.addons && item.addons.length > 0">
                                        <div class="mt-1 flex flex-wrap gap-1 pl-13 ml-[52px]">
                                            <template x-for="addon in item.addons" :key="addon.name">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-wider border border-amber-100 shadow-sm">
                                                    <span class="mr-1 text-amber-500">★</span> <span x-text="addon.quantity > 1 ? addon.quantity + 'x ' + addon.name : addon.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Notes -->
                                    <template x-if="item.notes">
                                        <div class="mt-1.5 pl-13 ml-[52px] text-[10px] font-bold text-red-500 uppercase italic tracking-widest bg-red-50/50 py-1 px-2 rounded-lg border border-red-50">
                                            * <span x-text="item.notes"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-6 pb-6 pt-2">
                            <button @click="markAsComplete(order.id)" 
                                    class="w-full py-4 text-white rounded-xl font-black text-sm uppercase tracking-widest transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center space-x-2.5 shadow-lg"
                                    :class="{
                                        'bg-red-600 hover:bg-red-700 shadow-red-200': isOverdue(order.created_at) && order.payment_status === 'Paid',
                                        'bg-smash-blue hover:bg-blue-700 shadow-blue-200': order.payment_status === 'Paid' && !isOverdue(order.created_at),
                                        'bg-gray-400 cursor-not-allowed opacity-50': order.payment_status === 'Pending'
                                    }"
                                    :disabled="order.payment_status === 'Pending'">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </main>

        @include('partials.sidebar', ['isOverlay' => true])
    </div>

    <script>
        function kdsApp() {
            return {
                orders: [],
                currentTime: '',
                now: new Date(),
                pollingInterval: null,
                clockInterval: null,

                init() {
                    this.updateTime();
                    this.clockInterval = setInterval(() => this.updateTime(), 1000);
                    
                    this.fetchOrders();
                    this.pollingInterval = setInterval(() => this.fetchOrders(), 5000);
                },

                updateTime() {
                    this.now = new Date();
                    this.currentTime = this.now.toLocaleTimeString('en-GB', { 
                        hour12: false, 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit' 
                    });
                },

                async fetchOrders() {
                    try {
                        const response = await fetch('{{ url('/api/kds/orders') }}');
                        this.orders = await response.json();
                    } catch (error) {
                        console.error('KDS Fetch Error:', error);
                    }
                },

                async markAsComplete(transactionId) {
                    // Optimistic UI: remove immediately
                    const originalOrders = [...this.orders];
                    this.orders = this.orders.filter(o => o.id !== transactionId);

                    try {
                        const response = await fetch(`{{ url('/api/kds/orders') }}/${transactionId}/complete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        if (!data.success) throw new Error('Update failed');
                        
                    } catch (error) {
                        console.error('KDS Update Error:', error);
                        // Revert on failure
                        this.orders = originalOrders;
                        alert('Gagal mengupdate pesanan. Silakan coba lagi.');
                    }
                },

                getElapsedTime(createdAt) {
                    // Using this.now ensures the timer re-renders every second
                    const diff = Math.floor((this.now - new Date(createdAt)) / 1000);
                    const mins = Math.floor(diff / 60);
                    const secs = diff % 60;
                    return `${mins}:${secs.toString().padStart(2, '0')}`;
                },

                isOverdue(createdAt) {
                    const diffMins = Math.floor((this.now - new Date(createdAt)) / 1000 / 60);
                    return diffMins >= 10;
                }
            };
        }
    </script>
</body>
</html>
