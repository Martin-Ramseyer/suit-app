<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Invitados por Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Selector de Eventos (sin cambios) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('eventos.historial') }}" method="GET">
                        <div class="flex items-end space-x-4">
                            <div class="flex-grow">
                                <x-input-label for="evento_id" :value="__('Selecciona un Evento para ver su Historial')" />
                                <select name="evento_id" id="evento_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required onchange="this.form.submit()">
                                    <option value="">-- Elige un evento --</option>
                                    @foreach($eventos as $evento)
                                        <option value="{{ $evento->id }}" {{ ($eventoSeleccionado && $eventoSeleccionado->id == $evento->id) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion ?? 'Sin descripción' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- =================== INICIO NUEVA SECCIÓN DE MÉTRICAS =================== --}}
            @if($eventoSeleccionado && $metricas)
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Métricas del Evento</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 uppercase">Invitados</h4>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $metricas['totalInvitados'] }} <span class="text-lg font-normal">totales</span></p>
                        <p class="mt-1 text-3xl font-semibold text-green-600">{{ $metricas['invitadosIngresaron'] }} <span class="text-lg font-normal">ingresaron</span></p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 uppercase">Beneficios Otorgados</h4>
                        <ul class="mt-2 space-y-1 text-gray-800">
                            @foreach($metricas['beneficios'] as $nombre => $cantidad)
                                <li class="flex justify-between">
                                    <span>{{ $nombre }}:</span>
                                    <span class="font-bold">{{ $cantidad }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 uppercase">Performance RRPP</h4>
                        <div class="mt-2 text-gray-800">
                            @if($metricas['topRrpp'])
                                <div class="mb-2">
                                    <span class="font-semibold text-green-700">Top Ingresos:</span>
                                    <p>{{ $metricas['topRrpp'] }}</p>
                                </div>
                                <div>
                                    <span class="font-semibold text-red-700">Menos Ingresos:</span>
                                    <p>{{ $metricas['bottomRrpp'] }}</p>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No hay datos de ingresos para RRPPs.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            {{-- =================== FIN NUEVA SECCIÓN DE MÉTRICAS =================== --}}

            {{-- Tabla de Resultados (sin cambios en la estructura de la tabla) --}}
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