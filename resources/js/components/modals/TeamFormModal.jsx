import React, { useState, useEffect } from "react";
import { useAppStore } from "@/core/store/AppStore";
import { teamsService } from "@/core/services/api/teams";
import { sellersService } from "@/core/services/api/sellers";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Upload, Shield } from "lucide-react";

export default function TeamFormModal({ open, onClose, team }) {
  const [formData, setFormData] = useState({
    name: "",
    code: "",
    logo_url: "",
    created_month: "",
    created_year: "",
    member_count: 0
  });

  const { state } = useAppStore();
  const sellers = state.sellers;

  useEffect(() => {
    if (team) {
      setFormData({
        name: team.name || "",
        code: team.code || "",
        logo_url: team.logo_url || "",
        created_month: team.created_month || "",
        created_year: team.created_year || "",
        member_count: team.member_count || 0
      });
    } else {
      const now = new Date();
      setFormData({
        name: "",
        code: "",
        logo_url: "",
        created_month: now.toLocaleString('pt-BR', { month: 'long' }),
        created_year: now.getFullYear().toString(),
        member_count: 0
      });
    }
  }, [team, open]);

  const { dispatch } = useAppStore();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      let response;
      if (team) {
        response = await teamsService.update(team.id, {
          name: formData.name,
          logo: formData.logo_url,
          metadata: {
            code: formData.code,
            created_month: formData.created_month,
            created_year: formData.created_year,
            member_count: formData.member_count,
          },
        });
      } else {
        response = await teamsService.create({
          name: formData.name,
          logo: formData.logo_url,
          metadata: {
            code: formData.code,
            created_month: formData.created_month,
            created_year: formData.created_year,
            member_count: formData.member_count,
          },
        });
      }

      if (response.success && response.data) {
        if (team) {
          dispatch({ type: 'UPDATE_TEAM', payload: response.data });
        } else {
          dispatch({ type: 'ADD_TEAM', payload: response.data });
        }
        onClose();
      }
    } catch (error) {
      console.error('Erro ao salvar equipe:', error);
      alert('Erro ao salvar equipe');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleImageUpload = async (e) => {
    const file = e.target.files?.[0];
    if (file) {
      // TODO: Implementar upload de arquivo quando backend estiver pronto
      // Por enquanto, usar URL local temporária
      const reader = new FileReader();
      reader.onloadend = () => {
        setFormData({ ...formData, logo_url: reader.result });
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onClose}>
      <DialogContent className="bg-[#0d1525] border-slate-800 text-white max-w-2xl">
        <DialogHeader>
          <DialogTitle className="text-white text-xl">
            {team ? "Editar Equipe" : "Criar Nova Equipe"}
          </DialogTitle>
          <p className="text-slate-400 text-sm">
            Configure as informações da equipe
          </p>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="mt-6 space-y-6">
          <div className="grid md:grid-cols-2 gap-6">
            {/* Left Column - Form */}
            <div className="space-y-4">
              <div>
                <Label className="text-slate-300">Nome da Equipe</Label>
                <Input
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="Digite o nome da equipe"
                  required
                  className="bg-slate-900/60 border-slate-700 text-white"
                />
              </div>

              <div>
                <Label className="text-slate-300">Código</Label>
                <Input
                  value={formData.code}
                  onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                  placeholder="Ex: 0911"
                  required
                  className="bg-slate-900/60 border-slate-700 text-white"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-slate-300">Mês</Label>
                  <Input
                    value={formData.created_month}
                    onChange={(e) => setFormData({ ...formData, created_month: e.target.value })}
                    placeholder="Junho"
                    className="bg-slate-900/60 border-slate-700 text-white"
                  />
                </div>

                <div>
                  <Label className="text-slate-300">Ano</Label>
                  <Input
                    value={formData.created_year}
                    onChange={(e) => setFormData({ ...formData, created_year: e.target.value })}
                    placeholder="2025"
                    className="bg-slate-900/60 border-slate-700 text-white"
                  />
                </div>
              </div>

              <div>
                <Label className="text-slate-300">Número de Membros</Label>
                <Input
                  type="number"
                  value={formData.member_count}
                  onChange={(e) => setFormData({ ...formData, member_count: parseInt(e.target.value) })}
                  placeholder="0"
                  className="bg-slate-900/60 border-slate-700 text-white"
                />
              </div>
            </div>

            {/* Right Column - Logo */}
            <div className="flex flex-col items-center justify-center">
              <div className="relative mb-6">
                <div className="w-32 h-32 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 p-1">
                  <div className="w-full h-full rounded-2xl bg-slate-900 flex items-center justify-center overflow-hidden">
                    {formData.logo_url ? (
                      <img src={formData.logo_url} alt="Logo" className="w-full h-full object-cover" />
                    ) : (
                      <Shield className="w-12 h-12 text-blue-400" />
                    )}
                  </div>
                </div>
              </div>

              <h3 className="text-white font-semibold mb-2">Logo da Equipe</h3>
              <p className="text-slate-400 text-sm text-center mb-4">
                Adicione uma identidade visual para a equipe
              </p>

              <label htmlFor="team-logo-upload">
                <Button
                  type="button"
                  variant="outline"
                  className="bg-blue-600/10 border-blue-600 text-blue-400 hover:bg-blue-600 hover:text-white cursor-pointer"
                  onClick={() => document.getElementById('team-logo-upload').click()}
                >
                  <Upload className="w-4 h-4 mr-2" />
                  Fazer upload
                </Button>
              </label>
              <input
                id="team-logo-upload"
                type="file"
                accept="image/*"
                onChange={handleImageUpload}
                className="hidden"
              />
            </div>
          </div>

          <div className="flex justify-end gap-3 pt-6 border-t border-slate-800">
            <Button
              type="button"
              variant="outline"
              onClick={onClose}
              className="bg-slate-800/50 border-slate-700 text-white hover:bg-slate-700"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              disabled={isSubmitting}
              className="bg-blue-600 hover:bg-blue-700"
            >
              {isSubmitting ? "Salvando..." : team ? "Atualizar" : "Criar Equipe"}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}