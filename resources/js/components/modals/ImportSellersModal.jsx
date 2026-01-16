import React, { useState } from "react";
import { useAppStore } from "@/core/store/AppStore";
import { sellersService } from "@/core/services/api/sellers";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Upload, Download, CheckCircle, XCircle, X } from "lucide-react";
import { motion } from "framer-motion";

export default function ImportSellersModal({ open, onClose }) {
  const [file, setFile] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [result, setResult] = useState(null);

  const handleFileChange = (e) => {
    const selectedFile = e.target.files?.[0];
    if (selectedFile) {
      setFile(selectedFile);
      setResult(null);
    }
  };

  const { dispatch } = useAppStore();

  const handleUpload = async () => {
    if (!file) return;

    setUploading(true);
    try {
      // TODO: Implementar upload e processamento de arquivo quando backend estiver pronto
      // Por enquanto, usar uma biblioteca de leitura de Excel (ex: xlsx)
      // ou implementar no backend
      
      // Exemplo de implementação futura:
      // 1. Upload do arquivo para o backend
      // 2. Backend processa o Excel e retorna dados
      // 3. Criar vendedores em lote
      
      // Por enquanto, mostrar mensagem informativa
      alert('Funcionalidade de importação será implementada quando o backend estiver disponível. Por enquanto, use a criação individual de vendedores.');
      
      setResult({
        success: false,
        error: "Funcionalidade em desenvolvimento. Backend necessário para processar arquivos Excel."
      });
    } catch (error) {
      setResult({
        success: false,
        error: error.message || "Erro ao importar vendedores"
      });
    } finally {
      setUploading(false);
    }
  };

  const handleDownloadTemplate = () => {
    // Create a simple CSV template
    const template = "name,email,cpf,department,access_level\nJoão Silva,joao@example.com,123.456.789-00,Vendas,vendedor\n";
    const blob = new Blob([template], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template_vendedores.csv';
    a.click();
    window.URL.revokeObjectURL(url);
  };

  const handleClose = () => {
    setFile(null);
    setResult(null);
    onClose();
  };

  return (
    <Dialog open={open} onOpenChange={handleClose}>
      <DialogContent className="bg-[#0d1525] border-slate-800 text-white max-w-2xl">
        <DialogHeader>
          <div className="flex items-center justify-between">
            <div>
              <DialogTitle className="text-white text-xl mb-2">Upload de lista</DialogTitle>
              <p className="text-slate-400 text-sm">
                Baixe o template de lista padrão{" "}
                <button
                  onClick={handleDownloadTemplate}
                  className="text-blue-400 hover:text-blue-300 underline"
                >
                  Clicando aqui.
                </button>
              </p>
              <p className="text-slate-400 text-sm">
                Envie um arquivo em xlsx ou xls para o Ranking de Vendas.
              </p>
              <p className="text-slate-400 text-sm">Clique no botão abaixo para enviar o arquivo.</p>
            </div>
            <button
              onClick={handleClose}
              className="p-2 hover:bg-slate-800 rounded-lg transition-colors"
            >
              <X className="w-5 h-5 text-slate-400" />
            </button>
          </div>
        </DialogHeader>

        <div className="mt-6">
          {!result ? (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              className="border-2 border-dashed border-blue-600/50 rounded-xl p-12 text-center hover:border-blue-600 transition-colors cursor-pointer"
              onClick={() => document.getElementById('file-upload').click()}
            >
              <Upload className="w-12 h-12 mx-auto mb-4 text-blue-400" />
              
              {file ? (
                <div>
                  <p className="text-white font-medium mb-2">{file.name}</p>
                  <p className="text-slate-400 text-sm">
                    {(file.size / 1024).toFixed(2)} KB
                  </p>
                </div>
              ) : (
                <div>
                  <p className="text-white font-medium mb-2">Upload de lista</p>
                  <p className="text-slate-400 text-sm mb-4">
                    Envie um arquivo em xlsx ou xls para o Ranking de Vendas.
                  </p>
                  <p className="text-slate-400 text-sm">
                    Clique no botão abaixo para enviar o arquivo.
                  </p>
                </div>
              )}

              <input
                id="file-upload"
                type="file"
                accept=".xlsx,.xls,.csv"
                onChange={handleFileChange}
                className="hidden"
              />

              <Button
                type="button"
                className="mt-6 bg-blue-600 hover:bg-blue-700"
                onClick={(e) => {
                  e.stopPropagation();
                  document.getElementById('file-upload').click();
                }}
              >
                Fazer upload
              </Button>
            </motion.div>
          ) : (
            <motion.div
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              className={`border-2 rounded-xl p-8 text-center ${
                result.success
                  ? "border-green-600/50 bg-green-950/20"
                  : "border-red-600/50 bg-red-950/20"
              }`}
            >
              {result.success ? (
                <>
                  <CheckCircle className="w-16 h-16 mx-auto mb-4 text-green-400" />
                  <h3 className="text-white font-bold text-xl mb-2">Importação Concluída!</h3>
                  <p className="text-slate-400">
                    {result.count} vendedor(es) importado(s) com sucesso.
                  </p>
                </>
              ) : (
                <>
                  <XCircle className="w-16 h-16 mx-auto mb-4 text-red-400" />
                  <h3 className="text-white font-bold text-xl mb-2">Erro na Importação</h3>
                  <p className="text-slate-400">{result.error}</p>
                </>
              )}

              <Button
                onClick={handleClose}
                className="mt-6 bg-slate-700 hover:bg-slate-600"
              >
                Fechar
              </Button>
            </motion.div>
          )}

          {file && !result && (
            <div className="flex justify-end gap-3 mt-6">
              <Button
                variant="outline"
                onClick={() => setFile(null)}
                className="bg-slate-800/50 border-slate-700 text-white hover:bg-slate-700"
              >
                Cancelar
              </Button>
              <Button
                onClick={handleUpload}
                disabled={uploading}
                className="bg-blue-600 hover:bg-blue-700"
              >
                {uploading ? "Importando..." : "Importar"}
              </Button>
            </div>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
}