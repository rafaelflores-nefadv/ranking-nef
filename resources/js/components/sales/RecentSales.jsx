import React from "react";
import { motion } from "framer-motion";
import { format } from "date-fns";
import { ptBR } from "date-fns/locale";
import { User, Calendar, FileText } from "lucide-react";

export default function RecentSales({ sales, sellers }) {
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value || 0);
  };

  const getSellerName = (sellerId) => {
    const seller = sellers.find(s => s.id === sellerId);
    return seller?.name || "Desconhecido";
  };

  if (sales.length === 0) {
    return (
      <div className="text-center py-12 text-slate-400">
        <FileText className="w-12 h-12 mx-auto mb-3 opacity-50" />
        <p>Nenhuma venda registrada ainda</p>
      </div>
    );
  }

  return (
    <div className="space-y-3 max-h-[400px] overflow-y-auto pr-2">
      {sales.map((sale, index) => (
        <motion.div
          key={sale.id}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.2, delay: index * 0.05 }}
          className="bg-slate-700/30 rounded-xl p-4 border border-slate-700/50 hover:border-slate-600/50 transition-all"
        >
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center">
                <span className="text-white font-bold text-sm">
                  {getSellerName(sale.seller_id)?.charAt(0)?.toUpperCase()}
                </span>
              </div>
              <div>
                <p className="text-white font-medium">{getSellerName(sale.seller_id)}</p>
                <div className="flex items-center gap-3 text-slate-400 text-sm">
                  {sale.client_name && (
                    <span className="flex items-center gap-1">
                      <User className="w-3 h-3" />
                      {sale.client_name}
                    </span>
                  )}
                  <span className="flex items-center gap-1">
                    <Calendar className="w-3 h-3" />
                    {sale.sale_date ? format(new Date(sale.sale_date), "dd MMM", { locale: ptBR }) : "—"}
                  </span>
                </div>
              </div>
            </div>
            <p className="text-emerald-400 font-bold text-lg">
              {formatCurrency(sale.amount)}
            </p>
          </div>
          {sale.description && (
            <p className="text-slate-400 text-sm mt-2 pl-13">{sale.description}</p>
          )}
        </motion.div>
      ))}
    </div>
  );
}