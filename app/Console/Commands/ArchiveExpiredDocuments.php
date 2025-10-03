<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Notifications\DocumentExpiredNotification;
use Illuminate\Support\Facades\Log;

class ArchiveExpiredDocuments extends Command
{
    protected $signature = 'documents:archive-expired';
    protected $description = 'Finds expired documents, archives them, and notifies users.';

    public function handle()
    {
        Log::info('Comando [documents:archive-expired] ejecutándose...');
        $this->info('Buscando documentos caducados para archivar...');

        // Buscamos documentos firmados que hayan pasado su fecha de caducidad
        $expiredDocuments = Document::where('status', 'signed')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredDocuments->isEmpty()) {
            $this->info('No se encontraron documentos caducados.');
            Log::info('No se encontraron documentos caducados.');
            return 0;
        }

        $this->info("Se encontraron {$expiredDocuments->count()} documentos para archivar.");

        foreach ($expiredDocuments as $document) {
            // 1. Cambiamos el estado
            $document->update(['status' => 'archived']);

            // 2. Notificamos al técnico que lo subió
            $uploader = $document->uploader;
            if ($uploader) {
                $uploader->notify(new DocumentExpiredNotification($document));
            }

            // 3. Notificamos a todos los que firmaron
            foreach ($document->signatures as $signature) {
                $signer = $signature->signer;
                if ($signer) {
                    $signer->notify(new DocumentExpiredNotification($document));
                }
            }
            $this->info("Documento #{$document->id} ({$document->original_filename}) archivado y usuarios notificados.");
        }

        $this->info('Proceso de archivado completado.');
        Log::info('Proceso de archivado completado.');
        return 0;
    }
}