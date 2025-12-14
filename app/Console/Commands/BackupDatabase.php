<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // اسم ملف النسخة الاحتياطية
            $filename = "backup-" . Carbon::now()->format('Y-m-d_H-i-s') . ".sql";
            
            // مسار حفظ النسخة
            $path = storage_path('app/backups/' . $filename);
            
            // التأكد من وجود المجلد
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            // بيانات الاتصال بقاعدة البيانات
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            
            // أمر mysqldump
            $command = "mysqldump --host={$dbHost} --port={$dbPort} ";
            $command .= "--user={$dbUser} --password={$dbPass} {$dbName} > {$path}";
            
            // تشغيل الأمر
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                $this->info(" تم إنشاء النسخة الاحتياطية: {$filename}");
                
                // حذف النسخ القديمة (تحتفظ بآخر 4 نسخ أسبوعية)
                $this->cleanOldBackups();
            } else {
                $this->error(" فشل إنشاء النسخة الاحتياطية");
            }
            
        } catch (\Exception $e) {
            $this->error(" خطأ: " . $e->getMessage());
        }
    }
    
    /**
     * حذف النسخ القديمة
     */
    private function cleanOldBackups()
    {
        $backupPath = storage_path('app/backups');
        
        if (!is_dir($backupPath)) {
            return;
        }
        
        // جلب جميع ملفات النسخ الاحتياطي
        $backups = glob($backupPath . '/backup-*.sql');
        
        // ترتيب من الأحدث للأقدم
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // حذف النسخ الزائدة عن 4
        if (count($backups) > 4) {
            for ($i = 4; $i < count($backups); $i++) {
                unlink($backups[$i]);
                $this->info("🗑️  تم حذف نسخة قديمة: " . basename($backups[$i]));
            }
        }
    }
}