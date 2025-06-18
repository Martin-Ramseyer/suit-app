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
                            <strong class="font-bold">¡Ups!</strong>
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
                        <!-- Nombre Completo -->
                        <div>
                            <x-input-label for="nombre_completo" :value="__('Nombre Completo del Invitado')" />
                            <x-text-input id="nombre_completo" class="block mt-1 w-full" type="text" name="nombre_completo" :value="old('nombre_completo', $invitado->nombre_completo)" required autofocus />
                        </div>

                        <!-- Número de Acompañantes -->
                        <div class="mt-4">
                            <x-input-label for="numero_acompanantes" :value="__('Cantidad de Acompañantes')" />
                            <x-text-input id="numero_acompanantes" class="block mt-1 w-full" type="number" name="numero_acompanantes" :value="old('numero_acompanantes', $invitado->numero_acompanantes)" required />
                        </div>

                        <!-- Evento -->
                        <div class="mt-4">
                            <x-input-label for="evento_id" :value="__('Seleccionar Evento')" />
                            <select name="evento_id" id="evento_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                @foreach($eventos as $evento)
                                    <option value="{{ $evento->id }}" {{ old('evento_id', $invitado->evento_id) == $evento->id ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }} - {{ $evento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Beneficios -->
                        @if(Auth::user()->rol === 'ADMIN')
                        <div class="mt-4">
                            <x-input-label :value="__('Asignar Beneficios (opcional)')" />
                            <div class="mt-2 space-y-2">
                                @php
                                    $beneficiosAsignados = $invitado->beneficios->pluck('id')->toArray();
                                @endphp
                                @foreach($beneficios as $beneficio)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="beneficios[]" value="{{ $beneficio->id }}" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        {{ in_array($beneficio->id, $beneficiosAsignados) ? 'checked' : '' }}>
                                    <span class="ms-2">{{ $beneficio->nombre_beneficio }}</span>
                                </label>
                                <br>
                                @endforeach
                            </div>
                        </div>
                        @endif


                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('invitados.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
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