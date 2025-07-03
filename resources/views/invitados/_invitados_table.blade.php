<div class="overflow-x-auto">
    <table class="min-w-full w-full divide-y divide-gray-200"> 
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Invitado</th>
                <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wider">Acompañantes</th>
                
            

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
                <tr class="{{ $invitado->ingreso ? 'bg-green-50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900">{{ $invitado->nombre_completo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-center text-gray-500">
                        @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                            <form action="{{ route('invitados.updateAcompanantes', $invitado) }}" method="POST" class="flex items-center justify-center">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="numero_acompanantes" value="{{ $invitado->numero_acompanantes }}" class="w-20 form-input rounded-md shadow-sm text-center" min="0">
                                <button type="submit" class="ml-2 inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    OK
                                </button>
                            </form>
                        @else
                            {{ $invitado->numero_acompanantes }}
                        @endif
                    </td>
                    
                   
                    
                    @if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO']))
                        <td class="px-6 py-4 text-base text-gray-500 align-top">
                            @forelse($invitado->beneficios as $beneficio)
                                <span class="inline-block bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold mr-2 mb-2">
                                    {{ $beneficio->nombre_beneficio }}
                                    (Cant: {{ $beneficio->pivot->cantidad }})
                                </span>
                            @empty
                                <span class="text-xs text-gray-400">Sin beneficios</span>
                            @endforelse
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
                    @php
                        // Correctly calculating colspan based on visible columns for the current user
                        $colspan = 1; // Nombre
                        $colspan++;   // Acompañantes
                        if(Auth::user()->rol == 'ADMIN') $colspan++; // Evento
                        if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO'])) $colspan++; // Beneficios
                        if(in_array(Auth::user()->rol, ['ADMIN', 'CAJERO'])) $colspan++; // RRPP
                        if(Auth::user()->rol == 'CAJERO') $colspan++; // Ingreso
                        if(in_array(Auth::user()->rol, ['ADMIN', 'RRPP'])) $colspan++; // Acciones
                    @endphp
                    <td colspan="{{ $colspan }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        @if(request('search'))
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
