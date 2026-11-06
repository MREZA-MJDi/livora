<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Media\ProductImageVariantGenerator;
use Illuminate\Console\Command;
use Throwable;

class RegenerateProductImageVariants extends Command
{
    protected $signature = 'media:products:regenerate-variants {--force : Regenerate variants even when all derivatives exist}';

    protected $description = 'Generate thumbnail, card and detail WebP variants from original product images without modifying originals.';

    public function handle(ProductImageVariantGenerator $generator): int
    {
        $media = Media::query()
            ->whereHas('productImages')
            ->where('mime_type', 'like', 'image/%')
            ->orderBy('id')
            ->get();

        $bar = $this->output->createProgressBar($media->count());
        $bar->start();
        $failed = 0;
        $skipped = 0;

        foreach ($media as $item) {
            try {
                if (! $this->option('force') && $item->thumbnail_path && $item->card_path && $item->detail_path) {
                    $skipped++;
                } else {
                    $generator->generate($item);
                }
            } catch (Throwable $exception) {
                $failed++;
                $this->newLine();
                $this->warn('Media #'.$item->id.': '.$exception->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Processed: '.($media->count() - $failed - $skipped).'; skipped: '.$skipped.'; failed: '.$failed);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
