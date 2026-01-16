import React, { useState, useEffect } from "react";
import { useAppStore } from "@/core/store/AppStore";
import { configService } from "@/core/services/api/config";
import { motion } from "framer-motion";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Building2, Upload, Save, Trash2, AlertTriangle } from "lucide-react";

export default function Settings() {
  const { state, dispatch } = useAppStore();
  const [activeTab, setActiveTab] = useState("general");
  const [config, setConfig] = useState({
    company_name: "",
    company_logo: "",
    cnpj: "",
    primary_color: "#3b82f6",
    secondary_color: "#8b5cf6",
    notifications_system_enabled: true,
    notifications_email_enabled: true,
    sounds_enabled: true,
    voice_enabled: false,
    auto_refresh: true
  });
  const [isLoading, setIsLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  // Carregar configuração
  useEffect(() => {
    if (state.config) {
      setConfig({
        company_name: state.config.name || "",
        company_logo: state.config.logo || "",
        cnpj: "",
        primary_color: state.config.primaryColor || "#3b82f6",
        secondary_color: state.config.secondaryColor || "#8b5cf6",
        notifications_system_enabled: state.config.notificationsSystemEnabled ?? true,
        notifications_email_enabled: state.config.notificationsEmailEnabled ?? true,
        sounds_enabled: state.config.soundsEnabled ?? true,
        voice_enabled: state.config.voiceEnabled ?? false,
        auto_refresh: state.config.realtimeEnabled ?? true,
      });
    }
  }, [state.config]);

  const handleSave = async () => {
    setSaving(true);
    try {
      const response = await configService.update({
        name: config.company_name,
        logo: config.company_logo,
        primaryColor: config.primary_color,
        secondaryColor: config.secondary_color,
        notificationsSystemEnabled: config.notifications_system_enabled,
        notificationsEmailEnabled: config.notifications_email_enabled,
        soundsEnabled: config.sounds_enabled,
        voiceEnabled: config.voice_enabled,
        realtimeEnabled: config.auto_refresh,
      });

      if (response.success && response.data) {
        dispatch({ type: 'SET_CONFIG', payload: response.data });
        alert('Configurações salvas com sucesso!');
      }
    } catch (error) {
      console.error('Erro ao salvar configurações:', error);
      alert('Erro ao salvar configurações');
    } finally {
      setSaving(false);
    }
  };

  const handleImageUpload = async (e) => {
    const file = e.target.files?.[0];
    if (file) {
      // TODO: Implementar upload de arquivo quando backend estiver pronto
      // Por enquanto, usar URL local temporária
      const reader = new FileReader();
      reader.onloadend = () => {
        setConfig({ ...config, company_logo: reader.result });
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="min-h-screen bg-[#0a0e1a] p-6">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8"
        >
          <h1 className="text-3xl font-bold text-white mb-2">Configurações</h1>
          <p className="text-slate-400">Gerencie as configurações do sistema</p>
        </motion.div>

        <div className="grid lg:grid-cols-4 gap-6">
          {/* Left Sidebar - Menu */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="lg:col-span-1"
          >
            <div className="bg-[#0d1525] rounded-2xl p-4 border border-slate-800/50 sticky top-24">
              <div className="space-y-2">
                <div className="mb-6">
                  <p className="text-slate-500 text-xs font-semibold mb-3">Conta</p>
                  <div className="space-y-1">
                    <p className="text-white text-sm font-medium">
                      {config.company_name || "Nabarete e Ferro Advogados Associados SS"}
                    </p>
                    <p className="text-slate-400 text-xs">{config.cnpj || "68/3f163756ed011375447d87"}</p>
                  </div>
                </div>

                <div className="space-y-1">
                  <button
                    onClick={() => setActiveTab("general")}
                    className={`w-full text-left px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                      activeTab === "general"
                        ? "bg-blue-600 text-white"
                        : "text-slate-400 hover:bg-slate-800/50 hover:text-white"
                    }`}
                  >
                    Informações gerais
                  </button>
                  <button className="w-full text-left px-4 py-2 hover:bg-slate-800/50 text-slate-400 hover:text-white rounded-lg text-sm transition-colors">
                    Informações de pagamento
                  </button>
                  <button
                    onClick={() => setActiveTab("notifications")}
                    className={`w-full text-left px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                      activeTab === "notifications"
                        ? "bg-blue-600 text-white"
                        : "text-slate-400 hover:bg-slate-800/50 hover:text-white"
                    }`}
                  >
                    Notificações
                  </button>
                </div>

                <div className="pt-4">
                  <p className="text-slate-500 text-xs font-semibold mb-2">Aparência</p>
                  <button className="w-full text-left px-4 py-2 hover:bg-slate-800/50 text-slate-400 hover:text-white rounded-lg text-sm transition-colors">
                    Temas do sistema
                  </button>
                </div>

                <div className="pt-4">
                  <p className="text-slate-500 text-xs font-semibold mb-2">Uso externo</p>
                  <button className="w-full text-left px-4 py-2 hover:bg-slate-800/50 text-slate-400 hover:text-white rounded-lg text-sm transition-colors">
                    Integrações
                  </button>
                </div>
              </div>
            </div>
          </motion.div>

          {/* Right Content - Settings Form */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            className="lg:col-span-3"
          >
            <div className="bg-[#0d1525] rounded-2xl p-6 border border-slate-800/50">
              {/* Header with actions */}
              <div className="flex items-center justify-between mb-6 pb-6 border-b border-slate-800">
                <div>
                  <h2 className="text-white font-bold text-xl mb-1">
                    {activeTab === "notifications" ? "Notificações" : "Informações gerais"}
                  </h2>
                  <p className="text-slate-400 text-sm">
                    {activeTab === "notifications"
                      ? "Ajuste como o sistema avisa sua equipe"
                      : "Configure as informações básicas de sua companhia"}
                  </p>
                </div>
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    className="bg-slate-800/50 border-slate-700 text-white hover:bg-slate-700"
                  >
                    Resetar
                  </Button>
                  <Button
                    onClick={handleSave}
                    disabled={saving}
                    className="bg-blue-600 hover:bg-blue-700"
                  >
                    <Save className="w-4 h-4 mr-2" />
                    {saving ? "Salvando..." : "Salvar"}
                  </Button>
                </div>
              </div>

              {/* Form */}
              {activeTab === "notifications" ? (
                <div className="space-y-6">
                  <div>
                    <h3 className="text-white font-semibold mb-2">Preferências de notificação</h3>
                    <p className="text-slate-400 text-sm mb-4">
                      Controle como o sistema envia alertas e avisos importantes.
                    </p>
                  </div>

                  <div className="space-y-4">
                    <div className="flex items-start justify-between gap-6 bg-slate-900/50 border border-slate-800 rounded-xl p-4">
                      <div>
                        <p className="text-white font-medium">Notificações do sistema</p>
                        <p className="text-slate-400 text-sm">
                          Exibe alertas no painel quando eventos importantes acontecerem.
                        </p>
                      </div>
                      <Switch
                        checked={config.notifications_system_enabled}
                        onCheckedChange={(value) =>
                          setConfig({ ...config, notifications_system_enabled: value })
                        }
                      />
                    </div>

                    <div className="flex items-start justify-between gap-6 bg-slate-900/50 border border-slate-800 rounded-xl p-4">
                      <div>
                        <p className="text-white font-medium">Notificações por e-mail</p>
                        <p className="text-slate-400 text-sm">
                          Envia resumos e alertas críticos para os responsáveis.
                        </p>
                      </div>
                      <Switch
                        checked={config.notifications_email_enabled}
                        onCheckedChange={(value) =>
                          setConfig({ ...config, notifications_email_enabled: value })
                        }
                      />
                    </div>

                    <div className="flex items-start justify-between gap-6 bg-slate-900/50 border border-slate-800 rounded-xl p-4">
                      <div>
                        <p className="text-white font-medium">Som de notificações</p>
                        <p className="text-slate-400 text-sm">
                          Reproduz sons ao concluir metas ou receber novos eventos.
                        </p>
                      </div>
                      <Switch
                        checked={config.sounds_enabled}
                        onCheckedChange={(value) =>
                          setConfig({ ...config, sounds_enabled: value })
                        }
                      />
                    </div>

                    <div className="flex items-start justify-between gap-6 bg-slate-900/50 border border-slate-800 rounded-xl p-4">
                      <div>
                        <p className="text-white font-medium">Voz</p>
                        <p className="text-slate-400 text-sm">
                          Ativa anúncios por voz para novos destaques e conquistas.
                        </p>
                      </div>
                      <Switch
                        checked={config.voice_enabled}
                        onCheckedChange={(value) =>
                          setConfig({ ...config, voice_enabled: value })
                        }
                      />
                    </div>
                  </div>
                </div>
              ) : (
                <div className="space-y-6">
                  {/* Company Info Section */}
                  <div>
                    <h3 className="text-white font-semibold mb-4">Informações gerais</h3>
                    <p className="text-slate-400 text-sm mb-4">Configure as informações básicas de sua companhia</p>
                    
                    <div className="grid md:grid-cols-2 gap-4">
                      <div>
                        <Label className="text-slate-300 mb-2">Nome da empresa</Label>
                        <Input
                          value={config.company_name}
                          onChange={(e) => setConfig({ ...config, company_name: e.target.value })}
                          placeholder="Nome legal"
                          className="bg-slate-900/60 border-slate-700 text-white"
                        />
                      </div>
                      
                      <div>
                        <Label className="text-slate-300 mb-2">Nome jurídico</Label>
                        <Input
                          placeholder="Nome legal"
                          className="bg-slate-900/60 border-slate-700 text-white"
                        />
                      </div>

                      <div className="md:col-span-2">
                        <Label className="text-slate-300 mb-2">CNPJ</Label>
                        <Input
                          value={config.cnpj}
                          onChange={(e) => setConfig({ ...config, cnpj: e.target.value })}
                          placeholder="19552415001180"
                          className="bg-slate-900/60 border-slate-700 text-white"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Logo Section */}
                  <div>
                    <h3 className="text-white font-semibold mb-2">Imagem de perfil</h3>
                    <p className="text-slate-400 text-sm mb-4">Coloque o logo da sua empresa no campo ao lado!</p>
                    
                    <div className="flex items-center gap-6">
                      <div className="w-32 h-32 rounded-xl bg-slate-900/60 border-2 border-dashed border-slate-700 flex items-center justify-center overflow-hidden">
                        {config.company_logo ? (
                          <img src={config.company_logo} alt="Logo" className="w-full h-full object-cover" />
                        ) : (
                          <Building2 className="w-12 h-12 text-slate-600" />
                        )}
                      </div>
                      
                      <div>
                        <p className="text-white font-medium mb-2">Upload de imagem</p>
                        <p className="text-slate-400 text-sm mb-3">
                          K07DBHCK96R [imagem do WhatsApp de 2023-08-05 à (s) 16:35:51_04a7f827.jpg]
                        </p>
                        <label htmlFor="logo-upload">
                          <Button
                            type="button"
                            variant="outline"
                            className="bg-blue-600/10 border-blue-600 text-blue-400 hover:bg-blue-600 hover:text-white cursor-pointer"
                            onClick={() => document.getElementById('logo-upload').click()}
                          >
                            <Upload className="w-4 h-4 mr-2" />
                            Fazer upload
                          </Button>
                        </label>
                        <input
                          id="logo-upload"
                          type="file"
                          accept="image/*"
                          onChange={handleImageUpload}
                          className="hidden"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Danger Zone */}
                  <div className="pt-6 border-t border-slate-800">
                    <h3 className="text-red-400 font-semibold mb-2 flex items-center gap-2">
                      <AlertTriangle className="w-5 h-5" />
                      Deletar companhia
                    </h3>
                    <p className="text-slate-400 text-sm mb-4">
                      Delete a sua conta do Ranking de Vendas
                    </p>
                    
                    <div className="bg-red-950/20 border border-red-900/30 rounded-xl p-4">
                      <h4 className="text-red-400 font-semibold mb-2">Aviso</h4>
                      <p className="text-slate-400 text-sm mb-4">
                        Aqui você pode deletar a sua companhia, uma vez deletada a conta não pode ser recuperada, então antes de executar essa ação tenha certeza.
                      </p>
                      <Button
                        variant="destructive"
                        className="bg-red-600 hover:bg-red-700"
                      >
                        <Trash2 className="w-4 h-4 mr-2" />
                        Deletar companhia
                      </Button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </motion.div>
        </div>
      </div>
    </div>
  );
}