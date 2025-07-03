<div class="overflow-x-auto">
    <table class="min-w-full w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Fecha del
                    Evento</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Descripción
                </th>
                <th class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($eventos as $evento)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $evento->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">
                        {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-base text-gray-500">{{ $evento->descripcion }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium">
                        <div class="flex items-center justify-end gap-x-3">
                            {{-- Toggle Activo --}}
                            <form action="{{ route('eventos.toggleActivo', $evento->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 text-sm font-semibold rounded-md border transition-colors duration-150
                                        {{ $evento->activo
                                            ? 'border-green-600 text-green-700 hover:bg-green-600 hover:text-white'
                                            : 'border-gray-400 text-gray-500 hover:bg-gray-400 hover:text-white' }}">
                                    {{ $evento->activo ? '● Activo' : '○ Activar' }}
                                </button>
                            </form>
                            {{-- Editar --}}
                            <a href="{{ route('eventos.edit', $evento->id) }}"
                                class="inline-block px-3 py-1.5 text-sm font-semibold text-indigo-700 border border-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                Editar
                            </a>
                            {{-- Eliminar --}}
                            <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este evento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow-sm transition-colors duration-150">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        No se encontraron eventos.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
