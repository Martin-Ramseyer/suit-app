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
            @if(in_array(Auth::user()->rol, ['RRPP', 'ADMIN']))
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

                    {{-- Filtros para ADMIN --}}
                    @if(Auth::user()->rol === 'ADMIN')
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
                    @else
                        {{-- Selector para RRPP/Cajero si hay MÁS DE UN evento futuro --}}
                        @if(in_array(Auth::user()->rol, ['RRPP', 'CAJERO']) && $eventosParaSelector->count() > 1)
                            <div class="mb-4">
                                <form id="evento-filter-form" action="{{ route('invitados.index') }}" method="GET">
                                    <x-input-label for="evento_id" :value="__('Selecciona un evento próximo')" />
                                    <select name="evento_id" id="evento-select-rol" class="w-full md:w-1/2 rounded-md shadow-sm border-gray-300" onchange="document.getElementById('evento-filter-form').submit();">
                                        @foreach($eventosParaSelector as $evento)
                                            <option value="{{ $evento->id }}" {{ ($eventoId == $evento->id) ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion ?? 'Sin descripción' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        @elseif($eventoSeleccionado)
                             <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-md">
                                Mostrando invitados para el evento del <strong>{{ \Carbon\Carbon::parse($eventoSeleccionado->fecha_evento)->format('d/m/Y') }}</strong>.
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md">
                                No hay eventos próximos activos en este momento.
                            </div>
                        @endif
                    @endif

                    <div id="invitados-table-container" class="overflow-x-auto">
                        @include('invitados._invitados_table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const eventoSelect = document.getElementById('evento-select');
            const tableContainer = document.getElementById('invitados-table-container');
            let debounceTimer;

            function fetchInvitados() {
                // Solo activa la búsqueda si los elementos existen (para Admin)
                if (!searchInput || !eventoSelect) return;

                const searchValue = searchInput.value;
                const eventoId = eventoSelect.value;
                const url = `{{ route('invitados.index') }}?search=${encodeURIComponent(searchValue)}&evento_id=${encodeURIComponent(eventoId)}`;

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                })
                .catch(error => console.error('Error fetching a los invitados:', error));
            }

            if(searchInput && eventoSelect) {
                searchInput.addEventListener('keyup', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(fetchInvitados, 300);
                });
                eventoSelect.addEventListener('change', fetchInvitados);
            }
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            document.getElementById('invitados-table-container').addEventListener('change', function(event) {
                if (event.target.classList.contains('ingreso-checkbox')) {
                    const checkbox = event.target;
                    const invitadoId = checkbox.dataset.id;
                    const isChecked = checkbox.checked;

                     function applyRowStyle(cb) {
                        const row = cb.closest('tr');
                        if (cb.checked) {
                            row.classList.add('bg-green-100');
                        } else {
                            row.classList.remove('bg-green-100');
                        }
                    }

                    fetch(`/invitados/${invitadoId}/toggle-ingreso`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ ingreso: isChecked })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                           applyRowStyle(checkbox);
                        } else {
                            alert(data.message || 'Hubo un error al actualizar el estado.');
                            checkbox.checked = !isChecked;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message || 'Hubo un error de conexión.');
                        checkbox.checked = !isChecked;
                    });
                }
            });

            document.querySelectorAll('.ingreso-checkbox').forEach(function (checkbox) {
                const row = checkbox.closest('tr');
                if (checkbox.checked) {
                    row.classList.add('bg-green-100');
                } else {
                    row.classList.remove('bg-green-100');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>