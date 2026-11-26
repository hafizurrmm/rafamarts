<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/** মিডিয়া লাইব্রেরি: ছবি আপলোড, দেখা, লিংক কপি ও মোছা (storage/app/public/media) */
class MediaLibrary extends Page implements HasForms
{
    use InteractsWithForms;

    private const DIR = 'media';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'মিডিয়া';

    protected static ?string $title = 'মিডিয়া লাইব্রেরি';

    protected static ?string $navigationGroup = 'ওয়েবসাইট';

    protected static ?int $navigationSort = 23;

    protected static ?string $slug = 'media';

    protected static string $view = 'filament.pages.media-library';

    public ?array $data = [];

    public string $search = '';

    public int $limit = 48;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\FileUpload::make('uploads')
                    ->label('ছবি আপলোড (একসাথে একাধিক বাছাই করা যাবে)')
                    ->multiple()
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->maxSize(5120)
                    ->maxFiles(20)
                    ->disk('public')
                    ->directory(self::DIR)
                    ->visibility('public')
                    ->panelLayout('grid')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => (Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image')
                            . '-' . Str::lower(Str::random(6)) . '.' . strtolower($file->getClientOriginalExtension())
                    ),
            ]);
    }

    public function upload(): void
    {
        $state = $this->form->getState(); // এই কলেই ফাইলগুলো ডিস্কে সেভ হয়
        $count = count($state['uploads'] ?? []);

        $this->form->fill();

        Notification::make()
            ->title($count ? "{$count}টি ছবি আপলোড হয়েছে" : 'কোনো ছবি বাছাই করা হয়নি')
            ->{$count ? 'success' : 'warning'}()
            ->send();
    }

    public function delete(string $path): void
    {
        // শুধু media ফোল্ডারের ভেতরের ফাইল মোছা যাবে
        if (! str_starts_with($path, self::DIR . '/') || str_contains($path, '..')) {
            return;
        }

        Storage::disk('public')->delete($path);

        Notification::make()->title('ছবি মোছা হয়েছে')->success()->send();
    }

    public function loadMore(): void
    {
        $this->limit += 48;
    }

    /** @return array{items: array<int, array<string, mixed>>, total: int} */
    public function files(): array
    {
        $disk = Storage::disk('public');
        $rows = [];

        foreach ($disk->files(self::DIR) as $path) {
            $name = basename($path);
            if (str_starts_with($name, '.')) {
                continue;
            }
            if ($this->search !== '' && ! str_contains(Str::lower($name), Str::lower(trim($this->search)))) {
                continue;
            }
            $rows[] = [
                'path' => $path,
                'name' => $name,
                'url' => url('storage/' . $path),
                'size' => $disk->size($path),
                'time' => $disk->lastModified($path),
            ];
        }

        usort($rows, fn ($a, $b) => $b['time'] <=> $a['time']);

        return ['items' => array_slice($rows, 0, $this->limit), 'total' => count($rows)];
    }
}
