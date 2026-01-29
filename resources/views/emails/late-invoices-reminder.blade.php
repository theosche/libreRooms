@extends('emails.layout')

@section('content')
    <h1>Factures en retard</h1>

    <p>Bonjour,</p>

    <div class="warning-box">
        <strong>{{ $lateCount }} facture{{ $lateCount > 1 ? 's' : '' }} en retard</strong>
        {{ $lateCount > 1 ? 'nécessitent' : 'nécessite' }} votre attention.
    </div>

    <p>
        Nous vous rappelons que certaines factures sont en attente de paiement et ont dépassé leur date d'échéance.
    </p>

    <p>
        Veuillez vérifier si ces factures ont été réglées et les marquer comme payées, ou envoyer un rappel de paiement si nécessaire.
    </p>

    <p>
        <a href="{{ route('invoices.index', ['view' => 'admin', 'status' => 'late']) }}" class="btn">
            Voir les factures en retard
        </a>
    </p>

    <p>Avec nos meilleures salutations,</p>
@endsection
