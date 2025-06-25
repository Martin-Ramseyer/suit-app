<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Invitados por Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Selector de Eventos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('eventos.historial') }}" method="GET">
                        <div class="flex items-end space-x-4">
                            <div class="flex-grow">
                                <x-input-label for="evento_id" :value="__('Selecciona un Evento para ver su Historial')" />
                                <select name="evento_id" id="evento_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                    <option value="">-- Elige un evento --</option>
                                    @foreach($eventos as $evento)
                                        <option value="{{ $evento->id }}" {{ ($eventoSeleccionado && $eventoSeleccionado->id == $evento->id) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion ?? 'Sin descripción' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-primary-button>
                                    {{ __('Ver Historial') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla de Resultados --}}
            @if($eventoSeleccionado)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">
                            Mostrando {{ $invitados->count() }} invitados para el evento del {{ \Carbon\Carbon::parse($eventoSeleccionado->fecha_evento)->format('d/m/Y') }}
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Invitado</th>
                                        <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Acompañantes</th>
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">RRPP</th>
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Beneficios</th>
                                        <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Ingresó</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
    @forelse ($invitados as $invitado)
        <tr class="{{ $invitado->ingreso ? 'bg-green-50' : '' }}">
            <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900">{{ $invitado->nombre_completo }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-base text-center text-gray-500">{{ $invitado->numero_acompanantes }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $invitado->rrpp->nombre_completo ?? 'N/A' }}</td>
            <td class="px-6 py-4 text-base text-gray-500">
                @forelse($invitado->beneficios as $beneficio)
                    <span class="inline-block bg-blue-100 text-blue-800 rounded-full px-2 py-1 text-xs font-semibold mr-1 mb-1">
                        {{ $beneficio->nombre_beneficio }} ({{ $beneficio->pivot->cantidad }})
                    </span>
                @empty
                    <span class="text-xs text-gray-400">Sin beneficios</span>
                @endforelse
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                @if($invitado->ingreso)
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Sí
                    </span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        No
                    </span>
                @endif
            </td>
        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                No se encontraron invitados para este evento.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
@endif

            {{-- Reemplazamos la condición @elseif --}}
            @if($eventoIdSeleccionado && !$eventoSeleccionado)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <div class="p-6 bg-white border-b border-gray-200 text-center text-gray-500">
                        El evento seleccionado no es válido o no tiene invitados. Por favor, selecciona otro de la lista.
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>