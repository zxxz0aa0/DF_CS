<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentFile extends Model
{
    protected $fillable = [
        'document_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'sort_order',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'file_url',
        'file_size_human',
    ];

    // 關聯
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // 存取器
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // 業務方法
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
        return $this->delete();
    }

    public function isPDF(): bool
    {
        return strtolower($this->file_type) === 'pdf';
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png']);
    }
}
