<x-app-layout>
    <x-slot name="header">
        {{-- **CAMBIO**: El título se adapta según el rol del usuario --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if (Auth::user()->rol === 'CAJERO')
                {{ __('Cargar Invitado en Puerta') }}
            @else
                {{ __('Cargar Nuevo Invitado') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">¡Ups! Hubo algunos problemas con tu entrada.</strong>
                            <ul class="list-disc mt-2 ml-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('invitados.store') }}" method="POST">
                        @csrf

                        <div>
                            <x-input-label for="nombre_completo" :value="__('Nombre Completo del Invitado')" />
                            <x-text-input id="nombre_completo" class="block mt-1 w-full" type="text"
                                name="nombre_completo" :value="old('nombre_completo')" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="numero_acompanantes" :value="__('Cantidad de Acompañantes')" />
                            <x-text-input id="numero_acompanantes" class="block mt-1 w-full" type="number"
                                name="numero_acompanantes" :value="old('numero_acompanantes', 0)" required min="0" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="evento_id" :value="__('Evento')" />

                            {{-- **CAMBIO**: Lógica condicional para el campo de evento --}}
                            @if (Auth::user()->rol === 'CAJERO')
                                {{-- Para el cajero, mostramos el evento activo y lo mandamos en un campo oculto --}}
                                @if ($eventos->first())
                                    <div class="block mt-1 w-full p-2 bg-gray-100 border border-gray-200 rounded-md">
                                        {{ \Carbon\Carbon::parse($eventos->first()->fecha_evento)->format('d/m/Y') }} -
                                        {{ $eventos->first()->descripcion ?? 'Evento Actual' }}
                                    </div>
                                    <input type="hidden" name="evento_id" value="{{ $eventos->first()->id }}">
                                @else
                                    <p class="text-red-500">Error: No se encontró un evento activo.</p>
                                @endif
                            @else
                                {{-- Para RRPP y Admin, mostramos el selector de siempre --}}
                                <select name="evento_id" id="evento_id"
                                    class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                    <option value="">-- Elige un evento --</option>
                                    @foreach ($eventos as $evento)
                                        <option value="{{ $evento->id }}"
                                            {{ old('evento_id') == $evento->id ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} -
                                            {{ $evento->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        {{-- La sección de beneficios ya está correctamente restringida solo para ADMIN --}}
                        @if (Auth::user()->rol === 'ADMIN')
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                {{-- ... código de beneficios sin cambios ... --}}
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-6 border-t border-gray-200 pt-4">
                            <a href="{{ route('invitados.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 underline">Cancelar</a>
                            <x-primary-button class="ms-4">
                                {{ __('Guardar Invitado') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
