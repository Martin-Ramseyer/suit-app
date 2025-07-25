<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">¡Ups!</strong>
                            <span class="block sm:inline">Hubo algunos problemas con tu entrada.</span>
                            <ul class="list-disc mt-2 ml-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- Nombre Completo -->
                        <div>
                            <x-input-label for="nombre_completo" :value="__('Nombre Completo')" />
                            <x-text-input id="nombre_completo" class="block mt-1 w-full" type="text"
                                name="nombre_completo" :value="old('nombre_completo', $usuario->nombre_completo)" required autofocus />
                        </div>

                        <!-- Usuario -->
                        <div class="mt-4">
                            <x-input-label for="usuario" :value="__('Usuario')" />
                            <x-text-input id="usuario" class="block mt-1 w-full" type="text" name="usuario"
                                :value="old('usuario', $usuario->usuario)" required />
                        </div>

                        <!-- Rol -->
                        <div class="mt-4">
                            <x-input-label for="rol" :value="__('Rol')" />
                            <select name="rol" id="rol"
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="RRPP" @if (old('rol', $usuario->rol) == 'RRPP') selected @endif>RRPP</option>
                                <option value="CAJERO" @if (old('rol', $usuario->rol) == 'CAJERO') selected @endif>Cajero</option>
                            </select>
                        </div>

                        <hr class="my-6">

                        <p class="text-sm text-gray-600">Deja los campos de contraseña en blanco para no cambiarla.</p>

                        <!-- Contraseña -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Nueva Contraseña')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('usuarios.index') }}"
                                class="underline text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                            <x-primary-button class="ms-4">
                                {{ __('Actualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
