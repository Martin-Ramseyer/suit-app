<x-app-layout>
        <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if(Auth::user()->rol == 'RRPP')
                    {{ __('Mis Invitados') }}
                @else
                    {{ __('Lista de Invitados') }}
                @endif
            </h2>
            @if(in_array(Auth::user()->rol, ['RRPP', 'ADMIN', 'CAJERO']))
                {{-- **CAMBIO**: Botón para cargar invitados solo visible para RRPP, Admin y Cajero --}}
                <a href="{{ route('invitados.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Cargar Invitado
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                     @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif


                    {{-- Filtros --}}
                    @if(Auth::user()->rol == 'ADMIN')
                        {{-- Filtros para ADMIN (sin cambios) --}}
                        <div class="mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-grow">
                                    <x-input-label for="search" :value="__('Buscar por Nombre o RRPP')" />
                                    <x-text-input type="text" id="search-input" name="search" class="w-full" :value="request('search')" />
                                </div>
                                <div class="flex-grow">
                                    <x-input-label for="evento_id" :value="__('Filtrar por Evento')" />
                                    <select name="evento_id" id="evento-select" class="w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Todos los eventos</option>
                                        @foreach($eventosParaSelector as $evento)
                                            <option value="{{ $evento->id }}" {{ ($eventoId == $evento->id) ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion ?? 'Sin descripción' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @elseif(Auth::user()->rol == 'RRPP' && $eventosParaSelector->isNotEmpty())
                        {{-- Filtros para RRPP (sin cambios) --}}
                         <div class="mb-4">
                            <form id="evento-filter-form" action="{{ route('invitados.index') }}" method="GET" class="flex items-center space-x-4">
                                <div class="flex-grow">
                                    <x-input-label for="evento_id" :value="__('Selecciona un evento próximo')" />
                                    <select name="evento_id" id="evento-select-rol" class="w-full rounded-md shadow-sm border-gray-300" onchange="this.form.submit()">
                                        @foreach($eventosParaSelector as $evento)
                                            <option value="{{ $evento->id }}" {{ ($eventoId == $evento->id) ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion ?? 'Sin descripción' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-grow">
                                     <x-input-label for="search" :value="__('Buscar por Nombre')" />
                                    <x-text-input type="text" id="search-input" name="search" class="w-full" :value="request('search')" />
                                </div>
                            </form>
                        </div>
                    @else
                        {{-- **CAMBIO**: Añadimos el buscador para el Cajero --}}
                        @if($eventoSeleccionado)
                             <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-md">
                                <p class="font-bold">Mostrando invitados para el evento del <strong>{{ \Carbon\Carbon::parse($eventoSeleccionado->fecha_evento)->format('d/m/Y') }}</strong></p>
                                <p>{{ $eventoSeleccionado->descripcion }}</p>
                            </div>
                            {{-- Formulario de búsqueda para el Cajero --}}
                            <div class="mb-4">
                                <form id="cajero-search-form" action="{{ route('invitados.index') }}" method="GET">
                                    <x-input-label for="search" :value="__('Buscar por Nombre de Invitado o RRPP')" />
                                    <x-text-input type="text" id="search-input-cajero" name="search" class="w-full" :value="request('search')" placeholder="Escribe un nombre para filtrar..." />
                                </form>
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md">
                                No hay ningún evento activo en este momento.
                            </div>
                        @endif
                    @endif

                    <div id="invitados-table-container">
                        @include('invitados._invitados_table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const tableContainer = document.getElementById('invitados-table-container');
        let debounceTimer;

        // --- MANEJADORES DE EVENTOS DE LA TABLA (DELEGADOS) ---
        // Se adjuntan una sola vez al contenedor principal.
        // Funcionarán para cualquier fila o botón, incluso los que se cargan después.

        if (tableContainer) {
            // Manejador para los checkboxes de "Ingreso"
            tableContainer.addEventListener('change', function(event) {
                if (event.target.classList.contains('ingreso-checkbox')) {
                    handleToggleIngreso(event.target);
                }
            });

            // NUEVO: Manejador para los inputs de "Acompañantes"
            tableContainer.addEventListener('input', function(event) {
                if (event.target.classList.contains('acompanantes-input')) {
                    handleAcompanantesInput(event.target);
                }
            });
        }

        // --- LÓGICA DE BÚSQUEDA Y FILTROS ---

        function fetchInvitados(searchQuery, eventoId) {
            const url = `{{ route('invitados.index') }}?search=${encodeURIComponent(searchQuery)}&evento_id=${encodeURIComponent(eventoId)}`;
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    // No es necesario re-adjuntar listeners gracias a la delegación de eventos.
                })
                .catch(error => console.error('Error al buscar invitados:', error));
        }

        // Listener para Admin
        const adminSearchInput = document.getElementById('search-input');
        const adminEventoSelect = document.getElementById('evento-select');
        if (adminSearchInput && adminEventoSelect) {
            const handleAdminSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchInvitados(adminSearchInput.value, adminEventoSelect.value);
                }, 300);
            };
            adminSearchInput.addEventListener('keyup', handleAdminSearch);
            adminEventoSelect.addEventListener('change', handleAdminSearch);
        }

        // Listener para RRPP (mantiene su funcionamiento original de recarga)
        const rrppSearchInput = document.getElementById('search-input');
        const rrppEventoForm = document.getElementById('evento-filter-form');
        if (rrppSearchInput && rrppEventoForm && !adminEventoSelect) { // Evita que se ejecute en la vista de Admin
            rrppSearchInput.addEventListener('keyup', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => rrppEventoForm.submit(), 500);
            });
        }

        // Listener para Cajero
        const cajeroSearchInput = document.getElementById('search-input-cajero');
        if (cajeroSearchInput) {
            const eventoIdCajero = "{{ $eventoSeleccionado->id ?? '' }}";
            cajeroSearchInput.addEventListener('keyup', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchInvitados(cajeroSearchInput.value, eventoIdCajero);
                }, 300);
            });
        }

        // --- FUNCIONES DE LÓGICA PARA LA TABLA ---

        function handleToggleIngreso(checkbox) {
            const invitadoId = checkbox.dataset.id;
            const isChecked = checkbox.checked;
            const row = checkbox.closest('tr');

            // Actualización optimista del UI
            row.classList.toggle('bg-green-50', isChecked);

            fetch(`/invitados/${invitadoId}/toggle-ingreso`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ ingreso: isChecked })
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => Promise.reject(err));
                return response.json();
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Hubo un error al actualizar el ingreso.');
                // Revertir el cambio si falla la petición
                checkbox.checked = !isChecked;
                row.classList.toggle('bg-green-50', !isChecked);
            });
        }

        // NUEVO: Función que muestra el botón "OK" al cambiar el número
        function handleAcompanantesInput(input) {
            const form = input.closest('form');
            if (form) {
                const okButton = form.querySelector('.acompanantes-ok-button');
                if (okButton) {
                    okButton.classList.remove('hidden');
                }
            }
        }
    });
</script>
    @endpush
</x-app-layout>
