<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Add New Manpower</h2>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('manpower.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP (7 digits)</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" required pattern="[0-9]{7}" maxlength="7" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono" placeholder="1298306">
                            @error('nip') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Type</label>
                            <select name="vehicle_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="2wh" {{ old('vehicle_type') == '2wh' ? 'selected' : '' }}>2 Wheels</option>
                                <option value="4wh" {{ old('vehicle_type') == '4wh' ? 'selected' : '' }}>4 Wheels</option>
                            </select>
                            @error('vehicle_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Type</label>
                            <select name="contract_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="dedicated" {{ old('contract_type') == 'dedicated' ? 'selected' : '' }}>Dedicated</option>
                                <option value="mitra" {{ old('contract_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                            </select>
                            @error('contract_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date (Day 1 Week 1)</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="mt-1 text-sm text-gray-500">This date will be used as Day 1 Week 1 for productivity calculation</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('manpower.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
