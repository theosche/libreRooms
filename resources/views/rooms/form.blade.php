@extends('layouts.app')

@section('title', isset($room) ? 'Modifier la salle' : 'Nouvelle salle')

@section('page-script')
    @vite(['resources/js/rooms/room-form.js'])
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ isset($room) ? 'Modifier la salle' : 'Nouvelle salle' }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($room) ? route('rooms.update', $room) : route('rooms.store') }}" class="styled-form">
            @csrf
            @if(isset($room))
                @method('PUT')
            @endif

            <!-- Informations de base -->
            <div class="form-group">
                <h3 class="form-group-title">Informations de base</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="owner_id" class="form-element-title">Propriétaire</label>
                        <select name="owner_id" id="owner_id" required>
                            <option value="">Sélectionner un propriétaire</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" @selected(old('owner_id', $room?->owner_id) == $owner->id)>
                                    {{ $owner->contact->display_name() }}
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="name" class="form-element-title">Nom de la salle</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $room?->name) }}"
                            required
                        >
                        <small class="text-gray-600">Le slug sera généré automatiquement à partir du nom</small>
                        @error('name')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="description" class="form-element-title">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                        >{{ old('description', $room?->description) }}</textarea>
                        @error('description')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input
                                type="hidden"
                                name="active"
                                value="0"
                            >
                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                @checked(old('active', $room?->active ?? true))
                            >
                            <span>Salle active (réservable)</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <!-- Visibilité -->
            <div class="form-group">
                <h3 class="form-group-title">Visibilité</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="is_public" value="0">
                            <input
                                type="checkbox"
                                name="is_public"
                                value="1"
                                @checked(old('is_public', $room?->is_public ?? true))
                            >
                            <span>Salle publique</span>
                        </label>
                        <small class="text-gray-600 block mt-1">
                            Si activé, la salle est visible et réservable par tous (y compris les visiteurs non connectés).<br>
                            Si désactivé, seuls les utilisateurs ayant un accès au propriétaire ou un accès direct à la salle pourront la voir et la réserver.
                        </small>
                    </div>
                </fieldset>
            </div>

            <!-- Tarification -->
            <div class="form-group">
                <h3 class="form-group-title">Tarification</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="price_mode" class="form-element-title">Mode de tarification</label>
                        <select name="price_mode" id="price_mode" required>
                            <option value="fixed" @selected(old('price_mode', $room?->price_mode?->value ?? 'fixed') == 'fixed')>Prix fixe</option>
                            <option value="free" @selected(old('price_mode', $room?->price_mode?->value) == 'free')>Libre participation</option>
                        </select>
                        <small class="text-gray-600">Si le prix libre est choisi, les paramètres suivants servent à calculé un prix suggéré</small>
                        @error('price_mode')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="price_short" class="form-element-title">Prix réservation courte</label>
                            <input
                                type="number"
                                id="price_short"
                                name="price_short"
                                step="0.01"
                                min="0"
                                value="{{ old('price_short', $room?->price_short ?? '') }}"
                            >
                            <small class="text-gray-600">Laisser vide pour désactiver</small>
                            @error('price_short')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="max_hours_short" class="form-element-title">Heures max pour réservation courte</label>
                            <input
                                type="number"
                                id="max_hours_short"
                                name="max_hours_short"
                                min="1"
                                value="{{ old('max_hours_short', $room?->max_hours_short) }}"
                                data-show-when="price_short"
                            >
                            <small class="text-gray-600">Requis si prix pour réservation courte défini</small>
                            @error('max_hours_short')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="price_full_day" class="form-element-title">Prix journée complète</label>
                        <input
                            type="number"
                            id="price_full_day"
                            name="price_full_day"
                            step="0.01"
                            min="0"
                            value="{{ old('price_full_day', $room?->price_full_day ?? '') }}"
                            required
                        >
                        @error('price_full_day')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="always_short_after" class="form-element-title">Toujours court après (heure)</label>
                            <input
                                type="number"
                                id="always_short_after"
                                name="always_short_after"
                                min="0"
                                max="24"
                                value="{{ old('always_short_after', $room?->always_short_after) }}"
                            >
                            <small class="text-gray-600">Ex: réservations après 17h ont toujours le tarif "court"</small>
                            @error('always_short_after')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="always_short_before" class="form-element-title">Toujours court avant (heure)</label>
                            <input
                                type="number"
                                id="always_short_before"
                                name="always_short_before"
                                min="0"
                                max="24"
                                value="{{ old('always_short_before', $room?->always_short_before) }}"
                            >
                            <small class="text-gray-600">Ex: réservations finies avant 12h ont toujours le tarif "court"</small>
                            @error('always_short_before')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="allow_late_end_hour" class="form-element-title">Heure de fin tardive autorisée</label>
                        <input
                            type="number"
                            id="allow_late_end_hour"
                            name="allow_late_end_hour"
                            min="0"
                            value="{{ old('allow_late_end_hour', $room?->allow_late_end_hour ?? 0) }}"
                        >
                        <small class="text-gray-600">Ex: si une réservation se termine le lendemain avant 3h du matin, on ne compte pas le jour suivant</small>
                        @error('allow_late_end_hour')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="use_special_discount" value="0">
                            <input
                                type="checkbox"
                                name="use_special_discount"
                                value="1"
                                @checked(old('use_special_discount', $room?->use_special_discount))
                            >
                            <span>Utiliser les réductions spéciales</span>
                        </label>
                    </div>
                    <small class="text-gray-600">Permet aux admins d'accorder des réductions spéciales au cas par cas</small>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="use_donation" value="0">
                            <input
                                type="checkbox"
                                name="use_donation"
                                value="1"
                                @checked(old('use_donation', $room?->use_donation))
                            >
                            <span>Autoriser les dons</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <!-- Règles de réservation -->
            <div class="form-group">
                <h3 class="form-group-title">Règles de réservation</h3>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="reservation_cutoff_days" class="form-element-title">Délai minimum (jours avant)</label>
                            <input
                                type="number"
                                id="reservation_cutoff_days"
                                name="reservation_cutoff_days"
                                min="0"
                                value="{{ old('reservation_cutoff_days', $room?->reservation_cutoff_days) }}"
                            >
                            @error('reservation_cutoff_days')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="reservation_advance_limit" class="form-element-title">Réservation max (jours à l'avance)</label>
                            <input
                                type="number"
                                id="reservation_advance_limit"
                                name="reservation_advance_limit"
                                min="0"
                                value="{{ old('reservation_advance_limit', $room?->reservation_advance_limit) }}"
                            >
                            @error('reservation_advance_limit')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Charte -->
            <div class="form-group">
                <h3 class="form-group-title">Charte</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="charter_mode" class="form-element-title">Type de charte</label>
                        <select name="charter_mode" id="charter_mode" required>
                            <option value="text" @selected(old('charter_mode', $room?->charter_mode?->value ?? 'text') == 'text')>Texte</option>
                            <option value="link" @selected(old('charter_mode', $room?->charter_mode?->value) == 'link')>Lien</option>
                            <option value="none" @selected(old('charter_mode', $room?->charter_mode?->value) == 'none')>Aucune</option>
                        </select>
                        @error('charter_mode')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element" id="charter_str_field">
                    <div class="form-field">
                        <label for="charter_str" class="form-element-title">
                            <span id="charter_str_label">Contenu de la charte</span>
                        </label>
                        <textarea
                            id="charter_str"
                            name="charter_str"
                            rows="4"
                        >{{ old('charter_str', $room?->charter_str) }}</textarea>
                        <small class="text-gray-600">Requis sauf si "Aucune" est sélectionné</small>
                        @error('charter_str')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <!-- Message secret -->
            <div class="form-group">
                <h3 class="form-group-title">Messages</h3>
                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="custom_message" class="form-element-title">Message personnalisé (transmis par email avec la confirmation de réservation)</label>
                        <textarea
                            id="custom_message"
                            name="custom_message"
                            rows="3"
                        >{{ old('custom_message', $room?->custom_message) }}</textarea>
                        @error('custom_message')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element" id="secret_message_field">
                    <div class="form-field">
                        <label for="secret_message" class="form-element-title">Message secret</label>
                        <textarea
                            id="secret_message"
                            name="secret_message"
                            rows="3"
                        >{{ old('secret_message', $room?->secret_message) }}</textarea>
                        <small class="text-gray-600">Par exemple pour transmettre le code de la salle. Le message peut être changé en tout temps et sera transmis par un lien, valable jusqu'à la fin de la réservation.</small>
                        @error('secret_message')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <!-- Configuration Calendrier -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration Calendrier</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="external_slot_provider" class="form-element-title">Fournisseur de créneaux externe</label>
                        <select name="external_slot_provider" id="external_slot_provider">
                            <option value="" @selected(is_null(old('external_slot_provider', $room?->external_slot_provider)))>Aucun</option>
                            <option value="caldav" id="caldav-option" @selected(old('external_slot_provider', $room?->external_slot_provider?->value) == 'caldav')>CalDAV</option>
                        </select>
                        @error('external_slot_provider')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element" id="dav_calendar_field">
                    <div class="form-field">
                        <label for="dav_calendar" class="form-element-title">Calendrier CalDAV</label>
                        <input
                            type="text"
                            id="dav_calendar"
                            name="dav_calendar"
                            value="{{ old('dav_calendar', $room?->dav_calendar) }}"
                        >
                        <small class="text-gray-600">Si le calendrier n'existe pas, le système essayera le créer.</small>
                        @error('dav_calendar')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="embed_calendar_mode" class="form-element-title">Mode d'intégration du calendrier</label>
                        <select name="embed_calendar_mode" id="embed_calendar_mode">
                            <option value="disabled" @selected(old('embed_calendar_mode', $room?->embed_calendar_mode?->value ?? 'disabled') == 'disabled')>Désactivé</option>
                            <option value="enabled" @selected(old('embed_calendar_mode', $room?->embed_calendar_mode?->value) == 'enabled')>Activé (formulaire utilisateur)</option>
                            <option value="admin_only" @selected(old('embed_calendar_mode', $room?->embed_calendar_mode?->value) == 'admin_only')>Admin uniquement</option>
                        </select>
                        <small class="text-gray-600">Définir si une vue calendrier de la salle doit être visible directement dans le formulaire de réservation.</small>
                        @error('embed_calendar_mode')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="calendar_view_mode" class="form-element-title">Mode d'affichage du calendrier</label>
                        <select name="calendar_view_mode" id="calendar_view_mode">
                            <option value="slot" @selected(old('calendar_view_mode', $room?->calendar_view_mode?->value ?? 'slot') == 'slot')>Créneaux uniquement</option>
                            <option value="title" @selected(old('calendar_view_mode', $room?->calendar_view_mode?->value) == 'title')>Titre de l'événement</option>
                            <option value="full" @selected(old('calendar_view_mode', $room?->calendar_view_mode?->value) == 'full')>Complet</option>
                        </select>
                        @error('calendar_view_mode')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <!-- Paramètres régionaux -->
            <div class="form-group">
                <h3 class="form-group-title">Paramètres régionaux (optionnel)</h3>
                <p class="text-sm text-gray-600 mb-4">Laissez vide pour utiliser les paramètres du propriétaire</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="timezone" class="form-element-title">Fuseau horaire</label>
                        @include('partials._timezone_select', [
                            'name' => 'timezone',
                            'id' => 'timezone',
                            'value' => old('timezone') ?? $room?->timezone,
                            'defaultTimezone' => $ownerTimezones[old('owner_id') ?? $room?->owner_id] ?? $systemSettings?->getTimezone() ?? 'Non défini',
                        ])
                        @error('timezone')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <div class="form-group">
                <h3 class="form-group-title">Emails</h3>
                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="disable_mailer" value="0">
                            <input
                                type="checkbox"
                                name="disable_mailer"
                                value="1"
                                @checked(old('disable_mailer', $room?->disable_mailer))
                            >
                            <span>Désactiver l'envoi d'emails pour cette salle</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('rooms.index', ['view' => 'mine']) }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($room) ? 'Mettre à jour' : 'Créer' }}
                </button>
                @if(isset($room))
                    <button type="button" onclick="confirmDeleteRoom()" class="btn btn-delete">
                        Supprimer
                    </button>
                @endif
            </div>
        </form>
        @if(isset($room))
            <form id="delete-room-form" action="{{ route('rooms.destroy', $room) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
<script>
    // Update timezone default when owner changes
    window.ownerTimezones = @json($ownerTimezones);
    window.ownersCaldavValid = @json($ownersCaldavValid);

    function confirmDeleteRoom() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')) {
            document.getElementById('delete-room-form').submit();
        }
    }
</script>
@endsection
