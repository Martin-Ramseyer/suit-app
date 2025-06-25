<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Métricas de RRPP: ') }} {{ $usuario->nombre_completo }}
            </h2>
            <a href="{{ route('usuarios.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                &larr; Volver a Usuarios
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('usuarios.metricas', $usuario->id) }}" method="GET">
                        <div class="flex items-end space-x-4">
                            <div class="flex-grow">
                                <x-input-label for="evento_id" :value="__('Filtrar por Evento')" />
                                <select name="evento_id" id="evento_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <option value="">-- Todos los Eventos --</option>
                                    @foreach($eventos as $evento)
                                        <option value="{{ $evento->id }}" {{ ($eventoSeleccionado && $eventoSeleccionado->id == $evento->id) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion ?? 'Sin descripción' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-primary-button>
                                    {{ __('Filtrar') }}
                                </x-primary-button>
                            </div>
                            @if($eventoSeleccionado)
                                <a href="{{ route('usuarios.metricas', $usuario->id) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Limpiar filtro
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Total Personas Invitadas</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $metricas['totalPersonas'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Total Ingresos</h3>
                    <p class="mt-1 text-3xl font-semibold text-green-600">{{ $metricas['totalIngresaron'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">No Ingresaron</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-600">{{ $metricas['totalNoIngresaron'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Tasa de Asistencia</h3>
                    <p class="mt-1 text-3xl font-semibold text-blue-600">{{ $metricas['tasaAsistencia'] }}%</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">
                        Detalle de Invitados
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Invitado</th>
                                    @if(!$eventoSeleccionado)
                                        <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Acompañantes</th>
                                    <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Ingresó</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($invitados as $invitado)
                                    <tr class="{{ $invitado->ingreso ? 'bg-green-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900">{{ $invitado->nombre_completo }}</td>
                                        @if(!$eventoSeleccionado)
                                            <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ \Carbon\Carbon::parse($invitado->evento->fecha_evento)->format('d/m/Y') }}</td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-base text-center text-gray-500">{{ $invitado->numero_acompanantes }}</td>
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
                                        <td colspan="{{ $eventoSeleccionado ? 3 : 4 }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            No se encontraron invitados para este RRPP.
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
</x-app-layout>