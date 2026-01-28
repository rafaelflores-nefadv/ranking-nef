# ⚙️ Configuração de Scheduler (Cron) e Fila (Queue) em Produção

## 📋 O que é necessário?

Para que rotinas automáticas funcionem (ex.: **processamento de ocorrências da API**, **renovação de temporadas**, **leitura por voz**, etc.), você precisa de:

1. **Scheduler (Cron)** executando o `schedule:run` a cada minuto
2. **Worker da fila (Queue)** rodando 24/7 (especialmente quando `QUEUE_CONNECTION=database`)

## 🔧 Configuração no Servidor

### Para Linux/Unix (cPanel, VPS, Servidor Dedicado)

#### 1. Acesse o crontab do servidor

```bash
crontab -e
```

#### 2. Adicione esta linha ao crontab

```bash
* * * * * cd /caminho/completo/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

**⚠️ IMPORTANTE:** Substitua `/caminho/completo/do/projeto` pelo caminho real do seu projeto no servidor.

**Exemplo:**
```bash
* * * * * cd /var/www/html/ranking-nef && php artisan schedule:run >> /dev/null 2>&1
```

#### 3. Salve e saia

- No **nano**: `Ctrl + X`, depois `Y`, depois `Enter`
- No **vi/vim**: `Esc`, depois `:wq`, depois `Enter`

#### 4. Verificar se está funcionando

```bash
# Verificar se o cronjob foi adicionado
crontab -l

# Testar manualmente
cd /caminho/do/projeto && php artisan schedule:run
```

---

## 🧵 Worker da Fila (Queue) — obrigatório para processamento assíncrono

O webhook da API e outras rotinas disparam jobs assíncronos. Se o `.env` estiver com `QUEUE_CONNECTION=database` (padrão do projeto), você precisa manter um worker rodando.

### Rodar manualmente (teste rápido)

```bash
cd /caminho/do/projeto
php artisan queue:work --tries=3 --timeout=90
```

### Rodar como serviço (Ubuntu/Debian com systemd) — recomendado

1) Crie o arquivo do serviço:

`/etc/systemd/system/ranking-nef-queue.service`

```ini
[Unit]
Description=Ranking NEF Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/ranking-nef
ExecStart=/usr/bin/php artisan queue:work --tries=3 --timeout=90
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

2) Ative e inicie:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now ranking-nef-queue
sudo systemctl status ranking-nef-queue
```

### Diagnóstico rápido (quando “dados chegam mas não processam”)

- **Verificar se há pendências** (ocorrências):
  - `php artisan tinker --execute="echo \\App\\Models\\ApiOccurrence::where('processed',0)->count().PHP_EOL;"`
- **Verificar jobs pendentes** (fila database):
  - `php artisan tinker --execute="echo \\Illuminate\\Support\\Facades\\DB::table('jobs')->count().PHP_EOL;"`
- **Verificar falhas**:
  - `php artisan queue:failed`
- **Ver logs do Laravel**:
  - `tail -f storage/logs/laravel.log`

---

### Para Windows Server (Task Scheduler)

#### 1. Abra o Task Scheduler (Agendador de Tarefas)

- Pressione `Win + R`
- Digite `taskschd.msc` e pressione Enter

#### 2. Crie uma Nova Tarefa

1. Clique em **"Criar Tarefa Básica"** ou **"Create Basic Task"**
2. Nome: `Laravel Scheduler`
3. Descrição: `Executa o scheduler do Laravel para renovação automática de temporadas`

#### 3. Configure o Gatilho (Trigger)

- **Tipo**: Diariamente (Daily)
- **Hora**: 00:00 (meia-noite)
- **Repetir a cada**: 1 minuto
- **Duração**: Indefinidamente

#### 4. Configure a Ação (Action)

- **Ação**: Iniciar um programa
- **Programa/script**: `C:\caminho\para\php.exe`
  - Exemplo: `C:\xampp\php\php.exe` ou `C:\php\php.exe`
- **Adicionar argumentos**: `C:\Projetos\ranking-nef\artisan schedule:run`
- **Iniciar em**: `C:\Projetos\ranking-nef`

#### 5. Salvar e Ativar

- Marque **"Abrir a caixa de diálogo Propriedades para esta tarefa quando eu clicar em Concluir"**
- Clique em **Concluir**
- Na aba **Geral**, marque **"Executar se o usuário estiver conectado ou não"**
- Na aba **Configurações**, marque **"Executar tarefa assim que possível após uma inicialização agendada ser perdida"**
- Clique em **OK**

---

### Para cPanel (Hospedagem Compartilhada)

#### 1. Acesse o cPanel

- Faça login no cPanel da sua hospedagem

#### 2. Encontre "Cron Jobs"

- Procure por **"Cron Jobs"** ou **"Tarefas Agendadas"** no menu

#### 3. Configure o Cronjob

- **Minuto**: `*` (todos os minutos)
- **Hora**: `*` (todas as horas)
- **Dia**: `*` (todos os dias)
- **Mês**: `*` (todos os meses)
- **Dia da Semana**: `*` (todos os dias da semana)

**Comando:**
```bash
cd /home/usuario/public_html/ranking-nef && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**⚠️ IMPORTANTE:**
- Substitua `/home/usuario/public_html/ranking-nef` pelo caminho real do seu projeto
- O caminho do PHP pode variar. Verifique com: `which php` ou `whereis php`
- Exemplos comuns: `/usr/bin/php`, `/usr/local/bin/php`, `/opt/cpanel/ea-php82/root/usr/bin/php`

#### 4. Adicionar Cronjob

- Clique em **"Adicionar Novo Cron Job"** ou **"Add New Cron Job"**

---

## ✅ Como Verificar se Está Funcionando

### 1. Testar Manualmente

Execute no servidor:

```bash
cd /caminho/do/projeto
php artisan schedule:run
```

Se aparecer mensagens como:
```
Running scheduled command: process-api-occurrences
Running scheduled command: ranking-voice
Running scheduled command: check-and-renew-seasons
```

Está funcionando! ✅

### 2. Verificar Logs

Os logs do Laravel ficam em:
```
storage/logs/laravel.log
```

Procure por mensagens relacionadas a `seasons:check-and-renew`

### 3. Verificar no Banco de Dados

Após a meia-noite, verifique se:
- A temporada antiga foi desativada (`is_active = false`)
- Uma nova temporada foi criada (`is_active = true`)
- Os pontos dos vendedores foram zerados

---

## 🕐 Quando o Comando Executa?

O comando `seasons:check-and-renew` está configurado para executar **diariamente à meia-noite (00:00)**.

Mas o cronjob precisa rodar **a cada minuto** para que o Laravel possa verificar se há comandos agendados para executar.

---

## 🔍 Troubleshooting (Solução de Problemas)

### Problema: Cronjob não está executando

**Solução:**
1. Verifique se o cronjob está ativo: `crontab -l`
2. Verifique os logs do sistema: `/var/log/cron` (Linux) ou logs do Task Scheduler (Windows)
3. Teste manualmente: `php artisan schedule:run`
4. Verifique permissões do arquivo: `chmod +x artisan`

### Problema: Caminho do PHP não encontrado

**Solução:**
1. Encontre o caminho do PHP: `which php` ou `whereis php`
2. Use o caminho completo no cronjob: `/usr/bin/php artisan schedule:run`

### Problema: Permissões negadas

**Solução:**
```bash
chmod +x artisan
chmod -R 775 storage bootstrap/cache
```

### Problema: Comando executa mas não renova temporada

**Solução:**
1. Verifique se a renovação automática está ativada nas configurações
2. Verifique se a temporada realmente terminou (data de término)
3. Verifique os logs: `tail -f storage/logs/laravel.log`

---

## 📝 Resumo

**O que você precisa fazer:**

1. ✅ Configurar um cronjob que execute `php artisan schedule:run` **a cada minuto**
2. ✅ O Laravel automaticamente executará `seasons:check-and-renew` **diariamente à meia-noite**
3. ✅ Verificar se está funcionando testando manualmente

**Comando do cronjob:**
```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

**Comando que será executado automaticamente:**
```bash
php artisan seasons:check-and-renew
```

---

## 🆘 Precisa de Ajuda?

Se tiver problemas, verifique:
- Logs do Laravel: `storage/logs/laravel.log`
- Logs do sistema (Linux): `/var/log/cron` ou `/var/log/syslog`
- Logs do Task Scheduler (Windows): Visualizar histórico da tarefa
