@props([
    'name' => 'profile_photo',
    'currentPath' => null,
    'label' => 'Foto de Perfil',
    'fallbackName' => null,
    'allowRemove' => false,
    'removeName' => null,
])

@php
    $displayName = $fallbackName ?? old('name', '');
    $fallbackSrc = \App\Support\AvatarHelper::dataUri($displayName, 128);
    $currentSrc = $currentPath ? asset('storage/' . $currentPath) : $fallbackSrc;
    $removeFieldName = $removeName ?? ('remove_' . $name);
@endphp

<div class="mb-4">
    <label class="block text-sm font-medium text-slate-300 mb-2">{{ $label }}</label>
    
    <!-- Preview da imagem -->
    <div class="mb-4 flex items-center gap-4">
        <div class="relative">
            <img id="avatar-preview-{{ $name }}" 
                 src="{{ $currentSrc }}" 
                 data-fallback-src="{{ $fallbackSrc }}"
                 alt="Preview" 
                 class="w-24 h-24 rounded-full object-cover border-2 border-slate-600">
            <div id="avatar-loading-{{ $name }}" class="hidden absolute inset-0 bg-slate-900/50 rounded-full flex items-center justify-center">
                <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <button type="button" 
                    onclick="openFileInput('{{ $name }}')" 
                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm transition-colors">
                Escolher Arquivo
            </button>
            <button type="button" 
                    onclick="openWebcam('{{ $name }}')" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">
                Tirar Foto
            </button>
            @if($allowRemove)
                <button type="button"
                        onclick="removeAvatar('{{ $name }}')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-sm transition-colors">
                    Remover Foto
                </button>
            @endif
        </div>
    </div>

    <!-- Input de arquivo oculto -->
    <input type="file" 
           id="avatar-input-{{ $name }}" 
           name="{{ $name }}" 
           accept="image/jpeg,image/png,image/webp" 
           class="hidden"
           onchange="handleFileSelect(event, '{{ $name }}')">
    
    <!-- Input hidden para base64 da webcam -->
    <input type="hidden" 
           id="avatar-base64-{{ $name }}" 
           name="{{ $name }}_base64">

    <input type="hidden"
           id="avatar-remove-{{ $name }}"
           name="{{ $removeFieldName }}"
           value="0">

    @error($name)
        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
    @enderror
    @error($name . '_base64')
        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
    @enderror
</div>

<!-- Modal da Webcam -->
<div id="webcam-modal-{{ $name }}" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 rounded-xl border border-slate-700 p-6 max-w-md w-full">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-white">Tirar Foto</h3>
            <button type="button" onclick="closeWebcam('{{ $name }}')" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="mb-4">
            <video id="webcam-video-{{ $name }}" 
                   autoplay 
                   playsinline 
                   class="w-full rounded-lg bg-slate-800"></video>
            <canvas id="webcam-canvas-{{ $name }}" class="hidden"></canvas>
        </div>
        
        <div class="flex gap-3">
            <button type="button" 
                    onclick="capturePhoto('{{ $name }}')" 
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                Capturar
            </button>
            <button type="button" 
                    onclick="closeWebcam('{{ $name }}')" 
                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
                Cancelar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let stream = null;

    function openFileInput(name) {
        document.getElementById('avatar-input-' + name).click();
    }

    function handleFileSelect(event, name) {
        const file = event.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                alert('Por favor, selecione um arquivo de imagem.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                updateAvatarPreview(e.target.result, name);
                // Limpar base64 da webcam se houver
                document.getElementById('avatar-base64-' + name).value = '';
                markAvatarNotRemoved(name);
            };
            reader.readAsDataURL(file);
        }
    }

    function openWebcam(name) {
        const modal = document.getElementById('webcam-modal-' + name);
        const video = document.getElementById('webcam-video-' + name);
        
        modal.classList.remove('hidden');
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
        })
        .catch(function(error) {
            console.error('Erro ao acessar webcam:', error);
            alert('Não foi possível acessar a webcam. Verifique as permissões do navegador.');
            closeWebcam(name);
        });
    }

    function closeWebcam(name) {
        const modal = document.getElementById('webcam-modal-' + name);
        const video = document.getElementById('webcam-video-' + name);
        
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        if (video.srcObject) {
            video.srcObject = null;
        }
        
        modal.classList.add('hidden');
    }

    function capturePhoto(name) {
        const video = document.getElementById('webcam-video-' + name);
        const canvas = document.getElementById('webcam-canvas-' + name);
        const context = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0);
        
        const imageData = canvas.toDataURL('image/jpeg', 0.8);
        updateAvatarPreview(imageData, name);
        
        // Salvar base64 no input hidden
        document.getElementById('avatar-base64-' + name).value = imageData;
        
        // Limpar input de arquivo
        document.getElementById('avatar-input-' + name).value = '';
        markAvatarNotRemoved(name);
        
        closeWebcam(name);
    }

    function updateAvatarPreview(imageSrc, name) {
        const preview = document.getElementById('avatar-preview-' + name);
        preview.src = imageSrc;
    }

    function removeAvatar(name) {
        const preview = document.getElementById('avatar-preview-' + name);
        const fallbackSrc = preview?.getAttribute('data-fallback-src');
        if (fallbackSrc) {
            preview.src = fallbackSrc;
        }
        document.getElementById('avatar-base64-' + name).value = '';
        document.getElementById('avatar-input-' + name).value = '';
        const removeInput = document.getElementById('avatar-remove-' + name);
        if (removeInput) {
            removeInput.value = '1';
        }
    }

    function markAvatarNotRemoved(name) {
        const removeInput = document.getElementById('avatar-remove-' + name);
        if (removeInput) {
            removeInput.value = '0';
        }
    }
</script>
@endpush
