@extends('layouts.app')

@section('title', 'Redefinir Senha')

@section('content')
<div class="min-h-screen bg-[#0a0e1a] p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Redefinir Senha</h1>
            <p class="text-slate-400">Defina uma nova senha para {{ $user->name }}</p>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl border border-slate-700/50 p-6">
            <form method="POST" action="{{ route('users.reset-password', $user) }}">
                @csrf
                @method('PUT')

                <!-- Senha -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Nova Senha</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 password-input"
                            placeholder="Digite uma senha forte">
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-300">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Indicador de força da senha -->
                    <div class="mt-2">
                        <div class="flex gap-1 mb-2">
                            <div id="strength-1" class="h-1 flex-1 rounded bg-slate-700"></div>
                            <div id="strength-2" class="h-1 flex-1 rounded bg-slate-700"></div>
                            <div id="strength-3" class="h-1 flex-1 rounded bg-slate-700"></div>
                            <div id="strength-4" class="h-1 flex-1 rounded bg-slate-700"></div>
                        </div>
                        <p id="strength-text" class="text-xs text-slate-400"></p>
                    </div>

                    <!-- Requisitos da senha -->
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center gap-2 text-xs">
                            <span id="req-length" class="text-slate-500">✓</span>
                            <span class="text-slate-400">Mínimo de 8 caracteres</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span id="req-uppercase" class="text-slate-500">✓</span>
                            <span class="text-slate-400">Pelo menos uma letra maiúscula</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span id="req-lowercase" class="text-slate-500">✓</span>
                            <span class="text-slate-400">Pelo menos uma letra minúscula</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span id="req-number" class="text-slate-500">✓</span>
                            <span class="text-slate-400">Pelo menos um número</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span id="req-special" class="text-slate-500">✓</span>
                            <span class="text-slate-400">Pelo menos um caractere especial (!@#$%^&*)</span>
                        </div>
                    </div>
                    
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Confirmar Senha</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 password-confirm-input"
                            placeholder="Digite a senha novamente">
                        <button type="button" id="togglePasswordConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-300">
                            <svg id="eyeIconConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeOffIconConfirmation" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="password-match-message" class="mt-2 text-xs"></div>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700">
                        Redefinir Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validação de senha
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const togglePasswordBtn = document.getElementById('togglePassword');
        const togglePasswordConfirmationBtn = document.getElementById('togglePasswordConfirmation');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');
        const eyeIconConfirmation = document.getElementById('eyeIconConfirmation');
        const eyeOffIconConfirmation = document.getElementById('eyeOffIconConfirmation');
        const passwordMatchMessage = document.getElementById('password-match-message');

        if (passwordInput && togglePasswordBtn) {
            // Toggle mostrar/ocultar senha
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.classList.toggle('hidden');
                eyeOffIcon.classList.toggle('hidden');
            });
        }

        if (passwordConfirmationInput && togglePasswordConfirmationBtn) {
            // Toggle mostrar/ocultar confirmação de senha
            togglePasswordConfirmationBtn.addEventListener('click', function() {
                const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirmationInput.setAttribute('type', type);
                eyeIconConfirmation.classList.toggle('hidden');
                eyeOffIconConfirmation.classList.toggle('hidden');
            });

            // Validar se as senhas coincidem
            passwordConfirmationInput.addEventListener('input', function() {
                validatePasswordMatch();
            });
        }

        if (passwordInput) {
            // Validação em tempo real
            passwordInput.addEventListener('input', function() {
                validatePassword(passwordInput.value);
                if (passwordConfirmationInput) {
                    validatePasswordMatch();
                }
            });

            function validatePassword(password) {
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
                };

                // Atualizar indicadores de requisitos
                updateRequirement('req-length', requirements.length);
                updateRequirement('req-uppercase', requirements.uppercase);
                updateRequirement('req-lowercase', requirements.lowercase);
                updateRequirement('req-number', requirements.number);
                updateRequirement('req-special', requirements.special);

                // Calcular força da senha
                const strength = calculateStrength(requirements);
                updateStrengthIndicator(strength);

                // Validar campo
                const isValid = Object.values(requirements).every(req => req === true);
                passwordInput.setCustomValidity(isValid ? '' : 'A senha não atende aos requisitos');
            }

            function validatePasswordMatch() {
                if (!passwordConfirmationInput || !passwordMatchMessage) return;
                
                const password = passwordInput.value;
                const confirmation = passwordConfirmationInput.value;

                if (confirmation.length === 0) {
                    passwordMatchMessage.textContent = '';
                    passwordMatchMessage.className = 'mt-2 text-xs';
                    passwordConfirmationInput.classList.remove('border-red-500', 'border-green-500');
                    passwordConfirmationInput.classList.add('border-slate-600');
                    passwordConfirmationInput.setCustomValidity('');
                    return;
                }

                if (password === confirmation) {
                    passwordMatchMessage.textContent = '✓ As senhas coincidem';
                    passwordMatchMessage.className = 'mt-2 text-xs text-green-400';
                    passwordConfirmationInput.classList.remove('border-red-500', 'border-slate-600');
                    passwordConfirmationInput.classList.add('border-green-500');
                    passwordConfirmationInput.setCustomValidity('');
                } else {
                    passwordMatchMessage.textContent = '✗ As senhas não coincidem';
                    passwordMatchMessage.className = 'mt-2 text-xs text-red-400';
                    passwordConfirmationInput.classList.remove('border-green-500', 'border-slate-600');
                    passwordConfirmationInput.classList.add('border-red-500');
                    passwordConfirmationInput.setCustomValidity('As senhas não coincidem');
                }
            }

            function updateRequirement(id, met) {
                const element = document.getElementById(id);
                if (element) {
                    if (met) {
                        element.textContent = '✓';
                        element.classList.remove('text-slate-500');
                        element.classList.add('text-green-400');
                    } else {
                        element.textContent = '✗';
                        element.classList.remove('text-green-400');
                        element.classList.add('text-slate-500');
                    }
                }
            }

            function calculateStrength(requirements) {
                let score = 0;
                if (requirements.length) score++;
                if (requirements.uppercase) score++;
                if (requirements.lowercase) score++;
                if (requirements.number) score++;
                if (requirements.special) score++;
                return score;
            }

            function updateStrengthIndicator(strength) {
                const strengthText = document.getElementById('strength-text');
                const colors = {
                    0: { color: 'bg-red-500', text: 'Muito fraca', textColor: 'text-red-400' },
                    1: { color: 'bg-red-500', text: 'Muito fraca', textColor: 'text-red-400' },
                    2: { color: 'bg-orange-500', text: 'Fraca', textColor: 'text-orange-400' },
                    3: { color: 'bg-yellow-500', text: 'Média', textColor: 'text-yellow-400' },
                    4: { color: 'bg-green-500', text: 'Forte', textColor: 'text-green-400' },
                    5: { color: 'bg-green-500', text: 'Muito forte', textColor: 'text-green-400' }
                };

                for (let i = 1; i <= 4; i++) {
                    const bar = document.getElementById(`strength-${i}`);
                    if (bar) {
                        bar.className = `h-1 flex-1 rounded ${i <= strength ? colors[strength].color : 'bg-slate-700'}`;
                    }
                }

                if (strengthText) {
                    if (strength === 0) {
                        strengthText.textContent = '';
                    } else {
                        strengthText.textContent = `Força da senha: ${colors[strength].text}`;
                        strengthText.className = `text-xs ${colors[strength].textColor}`;
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
