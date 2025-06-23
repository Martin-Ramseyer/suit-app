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
                    @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                    <div class="mb-4">
                        <form action="{{ route('invitados.index') }}" method="GET" class="flex items-center">
                            <x-text-input type="text" name="search" placeholder="Buscar por invitado o RRPP..." class="w-full" :value="request('search')" />
                            <x-primary-button class="ms-3">
                                {{ __('Buscar') }}
                            </x-primary-button>
                            @if(request('search'))
                                <a href="{{ route('invitados.index') }}" class="ms-3 text-sm text-gray-600 hover:text-gray-900 underline">
                                    Limpiar
                                </a>
                            @endif
                        </form>
                    </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full divide-y divide-gray-200"> 
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Invitado</th>
                                    <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Acompañantes</th>
                                    
                                    @if(Auth::user()->rol == 'RRPP')
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    @endif

                                     @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Beneficios</th>
                                     @endif
                                    
                                    @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">RRPP</th>
                                    @endif

                                    @if(Auth::user()->rol == 'CAJERO')
                                        <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Ingreso</th>
                                    @endif

                                    @if(in_array(Auth::user()->rol, ['ADMIN', 'RRPP']))
                                        <th scope="col" class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($invitados as $invitado)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900">{{ $invitado->nombre_completo }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-base text-center text-gray-500">{{ $invitado->numero_acompanantes }}</td>

                                        @if(Auth::user()->rol == 'RRPP')
                                            <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $invitado->evento->fecha_evento ? \Carbon\Carbon::parse($invitado->evento->fecha_evento)->format('d/m/Y') : 'N/A' }}</td>
                                        @endif
                                        
                                        @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                                            <td class="px-6 py-4 text-base text-gray-500 align-top">
                                                @foreach($invitado->beneficios as $beneficio)
                                                    <span class="inline-block bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold mr-2 mb-2">
                                                        {{ $beneficio->nombre_beneficio }}
                                                        (Cant: {{ $beneficio->pivot->cantidad }})
                                                    </span>
                                                @endforeach
                                            </td>
                                        @endif

                                        @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                                            <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $invitado->rrpp->nombre_completo ?? 'N/A' }}</td>
                                        @endif
                                        
                                        @if(Auth::user()->rol == 'CAJERO')
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <input type="checkbox" 
                                                       class="ingreso-checkbox h-6 w-6 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                                       data-id="{{ $invitado->id }}" 
                                                       {{ $invitado->ingreso ? 'checked' : '' }}>
                                            </td>
                                        @endif
                                        @if(in_array(Auth::user()->rol, ['ADMIN', 'RRPP']))
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium align-top">
                                                <form action="{{ route('invitados.destroy', $invitado->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este invitado?');">
                                                    <a href="{{ route('invitados.edit', $invitado->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- Modificamos el colspan para que se ajuste dinámicamente --}}
                                        @php
                                            $colspan = 2; // Invitado y Acompañantes
                                            if(Auth::user()->rol == 'RRPP') $colspan++; // Evento
                                            if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO'])) $colspan += 2; // Beneficios y RRPP
                                            if(Auth::user()->rol == 'CAJERO') $colspan++; // Ingreso
                                            if(in_array(Auth::user()->rol, ['ADMIN', 'RRPP'])) $colspan++; // Acciones
                                        @endphp
                                        <td colspan="{{ $colspan }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            @if(request('search'))
                                                No se encontraron invitados que coincidan con la búsqueda "{{ request('search') }}".
                                            @else
                                                No se encontraron invitados.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- El script para el toggle de ingreso no necesita cambios --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const checkboxes = document.querySelectorAll('.ingreso-checkbox');

            checkboxes.forEach(function (checkbox) {
                function applyRowStyle(cb) {
                    const row = cb.closest('tr');
                    if (cb.checked) {
                        row.classList.add('bg-green-100');
                    } else {
                        row.classList.remove('bg-green-100');
                    }
                }

                checkbox.addEventListener('change', function () {
                    const invitadoId = this.dataset.id;
                    const isChecked = this.checked;

                    fetch(`/invitados/${invitadoId}/toggle-ingreso`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ ingreso: isChecked })
                    })
                    .then(response => response.ok ? response.json() : Promise.reject('Error de red'))
                    .then(data => {
                        if (data.success) {
                           applyRowStyle(this);
                        } else {
                            alert('Hubo un error al actualizar el estado.');
                            this.checked = !isChecked;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error de conexión.');
                        this.checked = !isChecked;
                    });
                });
                
                applyRowStyle(checkbox);
            });
        });
    </script>
    @endpush
</x-app-layout>