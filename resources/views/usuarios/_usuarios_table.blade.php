<div class="overflow-x-auto">
    <table class="min-w-full w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    ID</th>
                <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Nombre Completo</th>
                <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Usuario</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th scope="col"
                    class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($usuarios as $usuario)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $usuario->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">{{ $usuario->nombre_completo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $usuario->usuario }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">{{ $usuario->rol }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium">
                        <div class="flex items-center justify-end gap-x-3">
                            {{-- Botón de Métricas --}}
                            @if ($usuario->rol === 'RRPP')
                                <a href="{{ route('usuarios.metricas', $usuario->id) }}"
                                    class="inline-block px-3 py-1.5 text-sm font-semibold text-green-700 border border-green-600 rounded-md hover:bg-green-600 hover:text-white transition-colors duration-150">
                                    Métricas
                                </a>
                            @endif

                            {{-- Botón de Editar --}}
                            <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                class="inline-block px-3 py-1.5 text-sm font-semibold text-indigo-700 border border-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                Editar
                            </a>

                            {{-- Botón de Eliminar --}}
                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');">
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
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        No se encontraron usuarios.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
