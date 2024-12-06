<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use App\Models\Transaction;
use Carbon\Carbon;

class Finanzas extends Component
{
    use WithPagination;

    #[Rule('required|string|max:255')]
    public $description;

    #[Rule('required|numeric|min:0')]
    public $amount;

    #[Rule('required|in:ingreso,egreso')]
    public $type;

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
        // Base query with date and type filters
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

        // Calculate totals separately
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

        // Paginate transactions
        return $baseQuery->latest()->paginate(10);
    }

    public function saveTransaction()
    {
        $validatedData = $this->validate();

        Transaction::create([
            'description' => $validatedData['description'],
            'amount' => $validatedData['amount'],
            'type' => $validatedData['type'],
        ]);

        $this->resetInput();
        $this->loadTransactions();
        
        session()->flash('success', 'Transacción agregada exitosamente.');
    }

    public function resetInput()
    {
        $this->reset(['description', 'amount', 'type']);
    }

    public function deleteTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        $this->loadTransactions();
        session()->flash('success', 'Transacción eliminada exitosamente.');
    }

    public function render()
    {
        return view('livewire.finanzas', [
            'transactions' => $this->loadTransactions()
        ]);
    }
}