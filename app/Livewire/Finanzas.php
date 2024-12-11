<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class Finanzas extends Component
{
    use WithPagination;

    #[Rule('required', message: 'La descripción es requerida')]
    #[Rule('string', message: 'La descripción debe ser un texto válido')]
    #[Rule('max:255', message: 'La descripción no puede exceder 255 caracteres')]
    public $description;

    #[Rule('required', message: 'El monto es requerido')]
    #[Rule('numeric', message: 'El monto debe ser un número')]
    #[Rule('min:0', message: 'El monto debe ser un número positivo')]
    public $amount;

    #[Rule('required', message: 'Debe seleccionar un tipo de transacción')]
    #[Rule('in:ingreso,egreso', message: 'El tipo de transacción solo puede ser Ingreso o Egreso')]
    public $type;

    public $editingTransactionId = null;
    public $editingTransaction = null;

    public $startDate;
    public $endDate;
    public $filterType = '';

    public $totalIngresos = 0;
    public $totalEgresos = 0;
    public $balance = 0;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        $baseQuery = Transaction::query()
            ->when($this->startDate && $this->endDate, function ($q) {
                return $q->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(), 
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            })
            ->when($this->filterType, function ($q) {
                return $q->where('type', $this->filterType);
            });

        $this->totalIngresos = Transaction::query()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(), 
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->where('type', 'ingreso')
            ->sum('amount');

        $this->totalEgresos = Transaction::query()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(), 
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->where('type', 'egreso')
            ->sum('amount');

        $this->balance = $this->totalIngresos - $this->totalEgresos;

        return $baseQuery->latest()->paginate(10);
    }

    public function saveTransaction()
    {
        try {
            $validatedData = $this->validate();

            Transaction::create([
                'description' => $validatedData['description'],
                'amount' => $validatedData['amount'],
                'type' => $validatedData['type'],
            ]);

            $this->resetInput();
            $this->loadTransactions();
            
            Notification::make()
                ->title('Transacción Agregada')
                ->body('La transacción se ha agregado exitosamente.')
                ->success()
                ->send();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $errorMessage = !empty($errors) ? $errors[0] : 'Error de validación';

            Notification::make()
                ->title('Error de Validación')
                ->body($errorMessage)
                ->danger()
                ->send();

            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo agregar la transacción: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetInput()
    {
        $this->reset(['description', 'amount', 'type']);
    }

    public function deleteTransaction($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmDelete($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();

            $this->loadTransactions();
            
            Notification::make()
                ->title('Transacción Eliminada')
                ->body('La transacción se ha eliminado exitosamente.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo eliminar la transacción: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function editTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $this->editingTransactionId = $id;
        $this->editingTransaction = $transaction;
        
        $this->description = $transaction->description;
        $this->amount = $transaction->amount;
        $this->type = $transaction->type;
    }

    public function updateTransaction()
    {
        try {
            $validatedData = $this->validate();

            $transaction = Transaction::findOrFail($this->editingTransactionId);

            $transaction->update([
                'description' => $validatedData['description'],
                'amount' => $validatedData['amount'],
                'type' => $validatedData['type'],
            ]);

            $this->resetInput();
            $this->editingTransactionId = null;
            $this->editingTransaction = null;

            $this->loadTransactions();
            
            Notification::make()
                ->title('Transacción Actualizada')
                ->body('La transacción se ha actualizado exitosamente.')
                ->success()
                ->send();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $errorMessage = !empty($errors) ? $errors[0] : 'Error de validación';

            Notification::make()
                ->title('Error de Validación')
                ->body($errorMessage)
                ->danger()
                ->send();

            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo actualizar la transacción: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelEdit()
    {
        $this->resetInput();
        $this->editingTransactionId = null;
        $this->editingTransaction = null;
    }

    public function render()
    {
        return view('livewire.finanzas', [
            'transactions' => $this->loadTransactions(),
            'isEditing' => $this->editingTransactionId !== null
        ]);
    }
}