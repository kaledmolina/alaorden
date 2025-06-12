<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Finanza; // Cambiar el modelo a 'Finanza'
use Carbon\Carbon;
use Filament\Notifications\Notification;

class Gastos extends Component
{ 
     use WithPagination;

    public $description;
    public $amount;
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
        $baseQuery = Finanza::query() // Cambiar de 'Transaction' a 'Finanza'
            ->when($this->startDate && $this->endDate, function ($q) {
                return $q->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(), 
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            })
            ->when($this->filterType, function ($q) {
                return $q->where('type', $this->filterType);
            });

        $this->totalIngresos = Finanza::query()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(), 
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->where('type', 'ingreso')
            ->sum('amount');

        $this->totalEgresos = Finanza::query()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(), 
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->where('type', 'gastos')
            ->sum('amount');

        $this->balance = $this->totalIngresos - $this->totalEgresos;

        return $baseQuery->latest()->paginate(10);
    }

    public function saveTransaction()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:ingreso,gastos',
        ]);

        Finanza::create([ // Cambiar 'Transaction' a 'Finanza'
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type,
        ]);

        $this->resetInput();
        $this->loadTransactions();

        Notification::make()
            ->title('Transacción Agregada')
            ->body('La transacción se ha agregado exitosamente.')
            ->success()
            ->send();
    }

    public function resetInput()
    {
        $this->reset(['description', 'amount', 'type']);
    }

    public function deleteTransaction($id)
    {
        $transaction = Finanza::findOrFail($id);
        $transaction->delete();

        $this->loadTransactions();

        Notification::make()
            ->title('Transacción Eliminada')
            ->body('La transacción se ha eliminado exitosamente.')
            ->success()
            ->send();
    }

    public function editTransaction($id)
    {
        $transaction = Finanza::findOrFail($id);

        $this->editingTransactionId = $id;
        $this->editingTransaction = $transaction;

        $this->description = $transaction->description;
        $this->amount = $transaction->amount;
        $this->type = $transaction->type;
    }

    public function updateTransaction()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:ingreso,gastos',
        ]);

        $transaction = Finanza::findOrFail($this->editingTransactionId);

        $transaction->update([
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type,
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
    }

    public function cancelEdit()
    {
        $this->resetInput();
        $this->editingTransactionId = null;
        $this->editingTransaction = null;
    }
    public function confirmDelete($id)
    {
        try {
            $transaction = Finanza::findOrFail($id);
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
    public function render()
    {
        return view('livewire.gastos',[
            'transactions' => $this->loadTransactions(),
            'isEditing' => $this->editingTransactionId !== null
        ]);
    }
}
