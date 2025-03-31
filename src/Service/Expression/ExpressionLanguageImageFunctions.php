<?php

namespace App\Service\Expression;

use App\Service\ImageFetcher;
use RuntimeException;
use SapientPro\ImageComparator\ImageComparator;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExpressionLanguageImageFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private ImageFetcher          $imageFetcher,
        private ImageComparator $imageComparator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'image_similarity',
                $this->uncompilableFunction(),
                $this->imageSimilarity(...),
            ),
        ];
    }

    private function imageSimilarity(array $context, string $imageUrl, string $hash): float
    {
        $testedImageHash = $this->imageFetcher->getImageHash($imageUrl);
        if ($testedImageHash === null) {
            throw new RuntimeException("Cannot test the image '{$imageUrl}' for similarity, it either returned an error or it's too large");
        }

        return $this->imageComparator->compareHashStrings($testedImageHash, $hash);
    }
}
