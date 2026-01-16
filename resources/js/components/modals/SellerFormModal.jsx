import React, { useState, useEffect } from "react";
import { useAppStore } from "@/core/store/AppStore";
import { sellersService } from "@/core/services/api/sellers";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Upload, Eye, EyeOff } from "lucide-react";

export default function SellerFormModal({ open, onClose, seller, teams }) {
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    cpf: "",
    avatar_url: "",
    access_level: "vendedor",
    team_id: "",
    department: "",
    password: ""
  });

  useEffect(() => {
    if (seller) {
      setFormData({
        name: seller.name || "",
        email: seller.email || "",
        cpf: seller.cpf || "",
        avatar_url: seller.avatar_url || "",
        access_level: seller.access_level || "vendedor",
        team_id: seller.team_id || "",
        department: seller.department || "",
        password: ""
      });
    } else {
      setFormData({
        name: "",
        email: "",
        cpf: "",
        avatar_url: "",
        access_level: "vendedor",
        team_id: "",
        department: "",
        password: ""
      });
    }
  }, [seller, open]);

  const { dispatch } = useAppStore();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      let response;
      if (seller) {
        response = await sellersService.update(seller.id, {
          name: formData.name,
          email: formData.email,
          avatar: formData.avatar_url,
          teamId: formData.team_id || undefined,
          status: 'active',
          metadata: {
            cpf: formData.cpf,
            access_level: formData.access_level,
            department: formData.department,
          },
        });
      } else {
        response = await sellersService.create({
          name: formData.name,
          email: formData.email,
          avatar: formData.avatar_url,
          teamId: formData.team_id || undefined,
          metadata: {
            cpf: formData.cpf,
            access_level: formData.access_level,
            department: formData.department,
          },
        });
      }

      if (response.success && response.data) {
        if (seller) {
          dispatch({ type: 'UPDATE_SELLER', payload: response.data });
        } else {
          dispatch({ type: 'ADD_SELLER', payload: response.data });
        }
        onClose();
      }
    } catch (error) {
      console.error('Erro ao salvar vendedor:', error);
      alert('Erro ao salvar vendedor');
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
        setFormData({ ...formData, avatar_url: reader.result });
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onClose}>
      <DialogContent className="bg-[#0d1525] border-slate-800 text-white max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-white text-xl">
            {seller ? "Editar Vendedor" : "Perfil de usuário"}
          </DialogTitle>
          <p className="text-slate-400 text-sm">
            {seller ? "Edite as informações do vendedor" : "Edite informações básicas sobre o seu perfil."}
          </p>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="mt-6">
          <Tabs defaultValue="info" className="w-full">
            <TabsList className="bg-slate-900/60 mb-6">
              <TabsTrigger value="info" className="data-[state=active]:bg-blue-600">
                Informações básicas
              </TabsTrigger>
              <TabsTrigger value="integrations">Integrações</TabsTrigger>
            </TabsList>

            <TabsContent value="info" className="space-y-6">
              <div className="grid md:grid-cols-2 gap-6">
                {/* Left Column - Form Fields */}
                <div className="space-y-4">
                  <div>
                    <Label className="text-slate-300">Nome</Label>
                    <Input
                      value={formData.name}
                      onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                      placeholder="Digite o seu nome"
                      required
                      className="bg-slate-900/60 border-slate-700 text-white"
                    />
                  </div>

                  <div>
                    <Label className="text-slate-300">CPF</Label>
                    <Input
                      value={formData.cpf}
                      onChange={(e) => setFormData({ ...formData, cpf: e.target.value })}
                      placeholder="xxx.xxx.xxx-xx"
                      className="bg-slate-900/60 border-slate-700 text-white"
                    />
                  </div>

                  <div>
                    <Label className="text-slate-300">E-mail</Label>
                    <Input
                      type="email"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      placeholder="mail@exemplo.com.br"
                      required
                      className="bg-slate-900/60 border-slate-700 text-white"
                    />
                  </div>

                  <div>
                    <Label className="text-slate-300">Nível de usuário</Label>
                    <Select
                      value={formData.access_level}
                      onValueChange={(value) => setFormData({ ...formData, access_level: value })}
                    >
                      <SelectTrigger className="bg-slate-900/60 border-slate-700 text-white">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent className="bg-slate-900 border-slate-700">
                        <SelectItem value="vendedor" className="text-white">Vendedor</SelectItem>
                        <SelectItem value="gestor" className="text-white">Gestor</SelectItem>
                        <SelectItem value="admin" className="text-white">Administrador</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  {/* Security Section */}
                  <div className="pt-4">
                    <h3 className="text-white font-semibold mb-4">Segurança</h3>
                    <p className="text-slate-400 text-sm mb-4">Proteja o seu perfil com uma senha forte!</p>
                    
                    <div className="space-y-4">
                      <div className="relative">
                        <Label className="text-slate-300">Senha</Label>
                        <Input
                          type={showPassword ? "text" : "password"}
                          value={formData.password}
                          onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                          placeholder="••••••••"
                          className="bg-slate-900/60 border-slate-700 text-white pr-10"
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          className="absolute right-3 top-8 text-slate-400 hover:text-white"
                        >
                          {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        </button>
                      </div>

                      <div className="relative">
                        <Label className="text-slate-300">Confirmar senha</Label>
                        <Input
                          type={showPassword ? "text" : "password"}
                          placeholder="••••••••"
                          className="bg-slate-900/60 border-slate-700 text-white pr-10"
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          className="absolute right-3 top-8 text-slate-400 hover:text-white"
                        >
                          {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Right Column - Avatar Upload */}
                <div className="flex flex-col items-center justify-center">
                  <div className="relative mb-6">
                    <div className="w-48 h-48 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 p-1">
                      <div className="w-full h-full rounded-full bg-slate-900 flex items-center justify-center overflow-hidden">
                        {formData.avatar_url ? (
                          <img src={formData.avatar_url} alt="Avatar" className="w-full h-full object-cover" />
                        ) : (
                          <div className="w-full h-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                            <Upload className="w-12 h-12 text-purple-400" />
                          </div>
                        )}
                      </div>
                    </div>
                  </div>

                  <h3 className="text-white font-semibold mb-2">Carregar foto de perfil</h3>
                  <p className="text-slate-400 text-sm text-center mb-4">
                    Dê uma cara ao seu vendedor colocando uma foto!
                  </p>

                  <label htmlFor="avatar-upload">
                    <Button
                      type="button"
                      variant="outline"
                      className="bg-blue-600/10 border-blue-600 text-blue-400 hover:bg-blue-600 hover:text-white cursor-pointer"
                      onClick={() => document.getElementById('avatar-upload').click()}
                    >
                      <Upload className="w-4 h-4 mr-2" />
                      Fazer upload
                    </Button>
                  </label>
                  <input
                    id="avatar-upload"
                    type="file"
                    accept="image/*"
                    onChange={handleImageUpload}
                    className="hidden"
                  />
                </div>
              </div>
            </TabsContent>

            <TabsContent value="integrations">
              <div className="text-center py-12 text-slate-400">
                <p>Integrações em breve...</p>
              </div>
            </TabsContent>
          </Tabs>

          <div className="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-800">
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
              className="bg-green-600 hover:bg-green-700"
            >
              {isSubmitting ? "Salvando..." : "Criar colaborador"}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}