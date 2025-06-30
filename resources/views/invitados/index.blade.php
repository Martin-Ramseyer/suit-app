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
            let debounceTimer;

            function fetchInvitados(searchQuery, eventoId) {
                const url = `{{ route('invitados.index') }}?search=${encodeURIComponent(searchQuery)}&evento_id=${encodeURIComponent(eventoId)}`;
                const tableContainer = document.getElementById('invitados-table-container');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    attachCheckboxListeners(); // Re-adjuntar listeners a los nuevos checkboxes
                })
                .catch(error => console.error('Error al buscar invitados:', error));
            }
            
            // Listener para Admin
            const adminSearchInput = document.getElementById('search-input');
            const adminEventoSelect = document.getElementById('evento-select');
            if(adminSearchInput && adminEventoSelect) {
                const handleAdminSearch = () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        fetchInvitados(adminSearchInput.value, adminEventoSelect.value);
                    }, 300);
                };
                adminSearchInput.addEventListener('keyup', handleAdminSearch);
                adminEventoSelect.addEventListener('change', handleAdminSearch);
            }

            // Listener para RRPP
            const rrppSearchInput = document.getElementById('search-input'); // Reutiliza el ID si es único en su contexto
            const rrppEventoForm = document.getElementById('evento-filter-form');
             if (rrppSearchInput && rrppEventoForm) {
                 rrppSearchInput.addEventListener('keyup', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => rrppEventoForm.submit(), 500);
                });
            }

            // **NUEVO**: Listener para el buscador del Cajero
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

            // Función para manejar el toggle de ingreso
            function handleToggleIngreso(event) {
                if (event.target.classList.contains('ingreso-checkbox')) {
                    const checkbox = event.target;
                    const invitadoId = checkbox.dataset.id;
                    const isChecked = checkbox.checked;

                    const row = checkbox.closest('tr');
                    
                    // Optimistic update: cambia el color de la fila inmediatamente
                    if (isChecked) {
                        row.classList.add('bg-green-100');
                        row.classList.remove('bg-white');
                    } else {
                        row.classList.remove('bg-green-100');
                        row.classList.add('bg-white');
                    }

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
                        alert(error.message || 'Hubo un error de conexión.');
                        // Revertir el cambio si falla la petición
                        checkbox.checked = !isChecked;
                         if (checkbox.checked) {
                            row.classList.add('bg-green-100');
                            row.classList.remove('bg-white');
                        } else {
                            row.classList.remove('bg-green-100');
                            row.classList.add('bg-white');
                        }
                    });
                }
            }
            
            function attachCheckboxListeners() {
                const container = document.getElementById('invitados-table-container');
                container.removeEventListener('change', handleToggleIngreso); // Prevenir duplicados
                container.addEventListener('change', handleToggleIngreso);
                 
                container.querySelectorAll('.ingreso-checkbox').forEach(function (checkbox) {
                    const row = checkbox.closest('tr');
                     if (checkbox.checked) { row.classList.add('bg-green-100'); row.classList.remove('bg-white'); } 
                     else { row.classList.remove('bg-green-100'); row.classList.add('bg-white'); }
                });
            }

            attachCheckboxListeners();

        });
    </script>
    @endpush
</x-app-layout>
