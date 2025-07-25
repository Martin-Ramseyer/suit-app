<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Invitado') }}
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

                    <form action="{{ route('invitados.update', $invitado->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="nombre_completo" :value="__('Nombre Completo del Invitado')" />
                            <x-text-input id="nombre_completo" class="block mt-1 w-full" type="text"
                                name="nombre_completo" :value="old('nombre_completo', $invitado->nombre_completo)" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="numero_acompanantes" :value="__('Cantidad de Acompañantes')" />
                            <x-text-input id="numero_acompanantes" class="block mt-1 w-full" type="number"
                                name="numero_acompanantes" :value="old('numero_acompanantes', $invitado->numero_acompanantes)" required min="0" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="evento_id" :value="__('Seleccionar Evento')" />
                            <select name="evento_id" id="evento_id"
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                @foreach ($eventos as $evento)
                                    <option value="{{ $evento->id }}"
                                        {{ old('evento_id', $invitado->evento_id) == $evento->id ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} -
                                        {{ $evento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (Auth::user()->rol === 'ADMIN')
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                <x-input-label :value="__('Asignar Beneficios (opcional)')" class="mb-2 font-bold" />
                                <div class="space-y-3">
                                    @php
                                        $beneficiosAsignados = $invitado->beneficios->keyBy('id');
                                    @endphp
                                    @foreach ($beneficios as $beneficio)
                                        @php
                                            $asignado = $beneficiosAsignados->has($beneficio->id);
                                            $cantidad = $asignado
                                                ? $beneficiosAsignados[$beneficio->id]->pivot->cantidad
                                                : 1;
                                        @endphp
                                        <div class="flex items-center space-x-4 p-2 rounded-lg hover:bg-gray-50">
                                            <label class="flex items-center w-48 cursor-pointer">
                                                <input type="checkbox" name="beneficios[{{ $beneficio->id }}]"
                                                    value="{{ $beneficio->id }}"
                                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600"
                                                    {{ old("beneficios.{$beneficio->id}", $asignado) ? 'checked' : '' }}>
                                                <span
                                                    class="ms-3 text-gray-700">{{ $beneficio->nombre_beneficio }}</span>
                                            </label>
                                            <x-input-label for="cantidad_{{ $beneficio->id }}" :value="__('Cantidad:')" />
                                            <input type="number" name="cantidades[{{ $beneficio->id }}]"
                                                class="block w-28 rounded-md shadow-sm border-gray-300" min="1"
                                                value="{{ old("cantidades.{$beneficio->id}", $cantidad) }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-6 border-t border-gray-200 pt-4">
                            <a href="{{ route('invitados.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 underline">Cancelar</a>
                            <x-primary-button class="ms-4">
                                {{ __('Actualizar Invitado') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
