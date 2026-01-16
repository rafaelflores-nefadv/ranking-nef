import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { DollarSign } from "lucide-react";

export default function SaleForm({ open, onOpenChange, onSubmit, sellers, isLoading }) {
  const [formData, setFormData] = useState({
    seller_id: "",
    amount: "",
    description: "",
    client_name: "",
    sale_date: new Date().toISOString().split('T')[0]
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit({
      ...formData,
      amount: parseFloat(formData.amount)
    });
    setFormData({
      seller_id: "",
      amount: "",
      description: "",
      client_name: "",
      sale_date: new Date().toISOString().split('T')[0]
    });
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="bg-slate-900 border-slate-700 text-white">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2 text-white">
            <DollarSign className="w-5 h-5" />
            Registrar Venda
          </DialogTitle>
        </DialogHeader>
        
        <form onSubmit={handleSubmit} className="space-y-4 mt-4">
          <div className="space-y-2">
            <Label className="text-slate-300">Vendedor *</Label>
            <Select
              value={formData.seller_id}
              onValueChange={(value) => setFormData({ ...formData, seller_id: value })}
              required
            >
              <SelectTrigger className="bg-slate-800 border-slate-600 text-white">
                <SelectValue placeholder="Selecione o vendedor" />
              </SelectTrigger>
              <SelectContent className="bg-slate-800 border-slate-600">
                {sellers.map((seller) => (
                  <SelectItem key={seller.id} value={seller.id} className="text-white hover:bg-slate-700">
                    {seller.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="amount" className="text-slate-300">Valor da Venda *</Label>
            <Input
              id="amount"
              type="number"
              step="0.01"
              min="0"
              value={formData.amount}
              onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
              placeholder="0,00"
              required
              className="bg-slate-800 border-slate-600 text-white placeholder:text-slate-500"
            />
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="client" className="text-slate-300">Cliente</Label>
            <Input
              id="client"
              value={formData.client_name}
              onChange={(e) => setFormData({ ...formData, client_name: e.target.value })}
              placeholder="Nome do cliente"
              className="bg-slate-800 border-slate-600 text-white placeholder:text-slate-500"
            />
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="description" className="text-slate-300">Descrição</Label>
            <Input
              id="description"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="Descrição da venda"
              className="bg-slate-800 border-slate-600 text-white placeholder:text-slate-500"
            />
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="date" className="text-slate-300">Data da Venda</Label>
            <Input
              id="date"
              type="date"
              value={formData.sale_date}
              onChange={(e) => setFormData({ ...formData, sale_date: e.target.value })}
              className="bg-slate-800 border-slate-600 text-white"
            />
          </div>
          
          <Button
            type="submit"
            disabled={isLoading || !formData.seller_id}
            className="w-full bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700"
          >
            {isLoading ? "Registrando..." : "Registrar Venda"}
          </Button>
        </form>
      </DialogContent>
    </Dialog>
  );
}