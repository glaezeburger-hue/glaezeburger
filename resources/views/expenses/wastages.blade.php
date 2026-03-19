@extends('layouts.app')

@section('header', 'Expenses & Wastages')

@section('content')
<div class="space-y-6" x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }}, submitting: false }">

    {{-- Tabs Navigation --}}
    <div class="flex space-x-1 bg-gray-100/50 p-1.5 rounded-2xl w-max border border-gray-200">
        <a href="{{ route('expenses.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('expenses.*') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            Operating Expenses & Restock
        </a>
        <a href="{{ route('wastages.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('wastages.*') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            Wastage Tracking
        </a>
    </div>

    {{-- Summary & Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div class="flex gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 min-w-[200px]">
                <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wider flex justify-between">
                    <span>This Month</span>
                    <span class="text-red-500 font-bold">WASTAGE</span>
                </p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl font-bold text-red-600">{{ number_format($wastageCurrentMonth, 0, ',', '.') }}</h3>
                    <span class="text-sm text-gray-500 font-medium">incidents</span>
                </div>
            </div>
        </div>

        <button @click="openModal = true" class="flex items-center px-5 py-2.5 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Record Wastage
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
        <form action="{{ route('wastages.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue">
                <span class="text-gray-400">-</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue">
            </div>

            <button type="submit" class="p-2.5 bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 border border-gray-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            </button>
            
            @if(request()->anyFilled(['date_from', 'date_to']))
                <a href="{{ route('wastages.index') }}" class="text-sm text-rose-500 hover:text-rose-700 font-medium">Clear</a>
            @endif
        </form>
    </div>

    {{-- Wastage Table --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Material</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Recorded By</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($wastages as $wastage)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $wastage->wastage_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900">{{ $wastage->rawMaterial->name ?? 'Unknown' }}</span>
                            <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded w-max mt-1">
                                -{{ floatval($wastage->quantity) }} {{ $wastage->rawMaterial->unit ?? 'unit' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $reasonColors = [
                                'expired' => 'bg-orange-100 text-orange-800',
                                'damaged' => 'bg-red-100 text-red-800',
                                'spillage' => 'bg-amber-100 text-amber-800',
                                'other' => 'bg-gray-100 text-gray-800',
                            ];
                            $color = $reasonColors[$wastage->reason] ?? $reasonColors['other'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium uppercase {{ $color }}">
                            {{ $wastage->reason }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $wastage->description ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $wastage->user->name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <form action="{{ route('wastages.destroy', $wastage) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this wastage record? (This will restore the inventory stock)');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete & Rollback Stock">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 mb-4">
                            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No wastages recorded</h3>
                        <p class="text-sm text-gray-500">Good job reducing waste! Record incidents here when they happen.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($wastages->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $wastages->links() }}
        </div>
        @endif
    </div>

    {{-- Wastage Modal --}}
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openModal" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="openModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="openModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <form action="{{ route('wastages.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 py-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center" id="modal-title">
                            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Record Material Wastage
                        </h3>
                        <button type="button" @click="openModal = false" class="text-gray-400 hover:text-gray-500 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-5">
                        
                        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-sm text-red-800">
                            <strong>Warning:</strong> Recording wastage will immediately deduct stock from the selected raw material.
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Raw Material</label>
                            <select name="raw_material_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50" required>
                                <option value="" disabled selected>Select Material...</option>
                                @foreach($rawMaterials as $rm)
                                    <option value="{{ $rm->id }}" {{ old('raw_material_id') == $rm->id ? 'selected' : '' }}>{{ $rm->name }} ({{ $rm->unit }}) - Current Stock: {{ floatval($rm->stock) }}</option>
                                @endforeach
                            </select>
                            @error('raw_material_id') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Lost</label>
                                <input type="number" name="quantity" value="{{ old('quantity') }}" step="0.01" min="0.01" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50" required>
                                @error('quantity') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" name="wastage_date" value="{{ old('wastage_date', date('Y-m-d')) }}" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50" required>
                                @error('wastage_date') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <select name="reason" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50">
                                <option value="expired" {{ old('reason') == 'expired' ? 'selected' : '' }}>Expired / Basi</option>
                                <option value="damaged" {{ old('reason') == 'damaged' ? 'selected' : '' }}>Damaged / Rusak</option>
                                <option value="spillage" {{ old('reason') == 'spillage' ? 'selected' : '' }}>Spillage / Tumpah / Jatuh</option>
                                <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other / Lainnya</option>
                            </select>
                            @error('reason') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Detailed Description</label>
                            <input type="text" name="description" value="{{ old('description') }}" placeholder="e.g., Roti berjamur 2 pax" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50">
                            @error('description') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end space-x-3 rounded-b-3xl">
                        <button type="button" @click="openModal = false" :disabled="submitting" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors disabled:opacity-50">
                            Cancel
                        </button>
                        <button type="submit" @click="submitting = true" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-sm transition-colors disabled:opacity-50 flex items-center">
                            <span x-show="!submitting">Record & Deduct Stock</span>
                            <span x-show="submitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Recording...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
