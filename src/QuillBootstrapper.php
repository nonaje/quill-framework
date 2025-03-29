<?php

declare(strict_types=1);

namespace Quill;

final readonly class QuillBootstrapper
{
    public function __construct(
        private ContainerInterface $container,
        private Quill $quill,
    ) { }

    
}
