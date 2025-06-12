<div  class="p-6 bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <h1 class="text-2xl font-bold mb-4 dark:text-white">Gestión de Finanzas Herrero</h1>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($isEditing)
        {{-- Formulario de Edición --}}
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-lg font-bold mb-4 dark:text-white">Editar Transacción</h2>
            <form wire:submit.prevent="updateTransaction" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="description" class="block text-sm font-medium text-white-700 dark:text-gray-300">Descripción</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model="description"
                            placeholder="Descripción de la transacción"
                        />
                    </x-filament::input.wrapper>
                    @error('description')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-white-700 dark:text-gray-300">Monto</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="number"
                            wire:model="amount"
                            step="0.01"
                            placeholder="Monto de la transacción"
                        />
                    </x-filament::input.wrapper>
                    @error('amount')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-white-700 dark:text-gray-300">Tipo de Transacción</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="type">
                            <option value="">Seleccionar Tipo</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Gasto</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                    @error('type')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-end gap-4">
                        <x-filament::button 
                            type="submit" 
                            class="bg-green-500 hover:bg-green-600 text-white font-medium w-full"
                            color="success"
                        >
                            Guardar
                        </x-filament::button>
                        <x-filament::button 
                            type="button" 
                            wire:click="cancelEdit" 
                            class="bg-red-500 hover:bg-red-600 text-white font-medium w-full"
                            color="danger"
                        >
                            Cancelar
                        </x-filament::button>
                    </div>

            </form>
        </div>
    @else
        {{-- Formulario de Nueva Transacción --}}
        <x-filament::fieldset>
            <x-slot name="label">
                Ingrese en el formulario a continuación para agregar una nueva transacción.
            </x-slot>

            <div>
                <form wire:submit.prevent="saveTransaction" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="description" class="block text-sm font-medium text-white-700 dark:text-gray-300">Descripción</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model="description"
                            />
                        </x-filament::input.wrapper>
                        @error('description')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-white-700 dark:text-gray-300">Monto</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="number"
                                wire:model="amount"
                            />
                        </x-filament::input.wrapper>
                        @error('amount')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-white-700 dark:text-gray-300">Ingreso o Gasto?</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model="type">
                                <option value="">Seleccionar Tipo</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Gasto</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('type')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-end">                
                        <x-filament::button class="w-full" type="submit">                    
                            Guardar Transacción
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </x-filament::fieldset>

        <br>

        {{-- Filtros de Fecha --}}
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 flex flex-col md:flex-row gap-4 items-center">
            <div class="w-full md:flex-1">
                <label for="startDate" class="block text-sm font-medium text-white-700 dark:text-gray-300">Fecha inicio</label>
                <input 
                    type="date" 
                    wire:model.live="startDate" 
                    id="startDate" 
                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3
                    dark:bg-gray-700 dark:text-white">
            </div>
            <div class="w-full md:flex-1">
                <label for="endDate" class="block text-sm font-medium text-white-700 dark:text-gray-300">Fecha fin</label>
                <input 
                    type="date" 
                    wire:model.live="endDate" 
                    id="endDate" 
                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3
                    dark:bg-gray-700 dark:text-white">
            </div>
            <div class="w-full md:flex-1">
                <label for="filterType" class="block text-sm font-medium text-white-700 dark:text-gray-300">Filtrar por Tipo</label>
                <select 
                    wire:model.live="filterType" 
                    id="filterType" 
                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3
                    dark:bg-gray-700 dark:text-white">
                    <option value="">Todos los Tipos</option>
                    <option value="ingreso">Ingresos</option>
                    <option value="egreso">Gastos</option>
                </select>
            </div>
        </div>
        <br>    

        {{-- Resumen Financiero --}}
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-lg font-bold mb-4 dark:text-white">Resumen Financiero</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Ingresos</h3>
                    <p class="text-xl font-bold text-green-700 dark:text-green-400">${{ number_format($totalIngresos, 2) }}</p>
                </div>
                <div class="bg-red-100 dark:bg-red-900 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Gastos</h3>
                    <p class="text-xl font-bold text-red-700 dark:text-red-400">${{ number_format($totalEgresos, 2) }}</p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Balance</h3>
                    <p class="text-xl font-bold {{ $balance >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-red-700 dark:text-red-400' }}">${{ number_format($balance, 2) }}</p>
                </div>
            </div>
        </div>
        <br>

        {{-- Tabla de Transacciones --}}
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-lg font-bold mb-4 dark:text-white">Transacciones</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-white-700 dark:text-gray-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Monto</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 dark:text-white">{{ $transaction->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 dark:text-white">{{ $transaction->description }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $transaction->type == 'ingreso' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 
                                    {{ $transaction->type == 'ingreso' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    ${{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 flex gap-4">
                                    <x-filament::button wire:click="editTransaction({{ $transaction->id }})" color="info">
                                        Editar
                                    </x-filament::button>
                                    <x-filament::button 
                                        x-data
                                        x-on:click="$dispatch('open-modal', { id: 'delete-confirmation-{{ $transaction->id }}' })"
                                        color="danger"
                                    >
                                        Borrar
                                    </x-filament::button>
                                </td>

                                {{-- Confirmation Modal --}}
                                <x-filament::modal id="delete-confirmation-{{ $transaction->id }}">
                                    <x-slot name="header">
                                        <h2 class="text-xl font-bold">Confirmar Eliminación</h2>
                                    </x-slot>

                                    <div class="p-4">
                                        <p>¿Está seguro de que desea eliminar esta transacción?</p>
                                    </div>

                                    <x-slot name="footer">
                                        <div class="flex justify-end gap-4">
                                            <x-filament::button 
                                                color="secondary" 
                                                x-on:click="$dispatch('close-modal', { id: 'delete-confirmation-{{ $transaction->id }}' })"
                                            >
                                                Cancelar
                                            </x-filament::button>
                                            <x-filament::button 
                                                color="danger" 
                                                wire:click="confirmDelete({{ $transaction->id }})"
                                                x-on:click="$dispatch('close-modal', { id: 'delete-confirmation-{{ $transaction->id }}' })"
                                            >
                                                Eliminar
                                            </x-filament::button>
                                        </div>
                                    </x-slot>
                                </x-filament::modal>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    No hay transacciones en este período
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div><br>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    @endif
</div>