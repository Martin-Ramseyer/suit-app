<div class="overflow-x-auto">
    <table class="min-w-full w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Invitado</th>
                <th scope="col"
                    class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Acompañantes
                </th>

                @if (in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                    <th scope="col"
                        class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Beneficios
                    </th>
                @endif

                @if (in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                    <th scope="col"
                        class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">RRPP</th>
                @endif

                @if (Auth::user()->rol == 'CAJERO')
                    <th scope="col"
                        class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Ingreso
                    </th>
                @endif

                @if (in_array(Auth::user()->rol, ['ADMIN', 'RRPP']))
                    <th scope="col"
                        class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Acciones
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($invitados as $invitado)
                {{-- Se añade transición de color para el cambio de estado --}}
                <tr class="{{ $invitado->ingreso ? 'bg-green-50' : '' }} transition-colors duration-300">
                    <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900">
                        {{ $invitado->nombre_completo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-center text-gray-500">
                        @if (in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                            <form action="{{ route('invitados.updateAcompanantes', $invitado) }}" method="POST"
                                class="flex items-center justify-center">
                                @csrf
                                @method('PATCH')

                                {{-- Input con una clase específica para el script --}}
                                <input type="number" name="numero_acompanantes"
                                    value="{{ $invitado->numero_acompanantes }}"
                                    class="acompanantes-input w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-center"
                                    min="0">

                                {{-- Botón "OK" oculto por defecto y con estilo mejorado --}}
                                <button type="submit"
                                    class="acompanantes-ok-button hidden ml-2 px-2.5 py-1.5 text-xs font-semibold !text-white !bg-green-600 rounded-md shadow-sm !hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-150">
                                    OK
                                </button>
                            </form>
                        @else
                            {{ $invitado->numero_acompanantes }}
                        @endif
                    </td>

                    @if (in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                        {{-- Se mantiene el align-top para celdas con posible contenido múltiple --}}
                        <td class="px-6 py-4 text-base text-gray-500 align-top">
                            @forelse($invitado->beneficios as $beneficio)
                                {{-- Estilo de "píldora" para los beneficios --}}
                                <span
                                    class="inline-block bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold mr-2 mb-2">
                                    {{ $beneficio->nombre_beneficio }}
                                    (Cant: {{ $beneficio->pivot->cantidad }})
                                </span>
                            @empty
                                <span class="text-xs text-gray-400">Sin beneficios</span>
                            @endforelse
                        </td>
                    @endif

                    @if (in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                        <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">
                            {{ $invitado->rrpp->nombre_completo ?? 'N/A' }}</td>
                    @endif

                    @if (Auth::user()->rol == 'CAJERO')
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            {{-- Checkbox de ingreso estilizado --}}
                            <input type="checkbox"
                                class="ingreso-checkbox h-6 w-6 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                data-id="{{ $invitado->id }}" {{ $invitado->ingreso ? 'checked' : '' }}>
                        </td>
                    @endif
                    @if (in_array(Auth::user()->rol, ['ADMIN', 'RRPP']))
                        <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium align-top">
                            {{-- Contenedor Flex para alinear botones de acción --}}
                            <div class="flex items-center justify-end gap-x-3">
                                {{-- Botón de Editar --}}
                                <a href="{{ route('invitados.edit', $invitado->id) }}"
                                    class="inline-block px-3 py-1.5 text-sm font-semibold text-indigo-700 border border-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                    Editar
                                </a>
                                {{-- Botón de Eliminar --}}
                                <form action="{{ route('invitados.destroy', $invitado->id) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este invitado?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow-sm transition-colors duration-150">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    @php
                        // Se mantiene la lógica para calcular el colspan dinámicamente
                        $colspan = 2; // Invitado + Acompañantes
                        if (in_array(Auth::user()->rol, ['ADMIN', 'CAJERO'])) {
                            $colspan += 2;
                        } // Beneficios + RRPP
                        if (Auth::user()->rol == 'CAJERO') {
                            $colspan++;
                        } // Ingreso
                        if (in_array(Auth::user()->rol, ['ADMIN', 'RRPP'])) {
                            $colspan++;
                        } // Acciones
                    @endphp
                    <td colspan="{{ $colspan }}"
                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        @if (request('search'))
                            No se encontraron invitados que coincidan con la búsqueda.
                        @else
                            No hay invitados para mostrar.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
