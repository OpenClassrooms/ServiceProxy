<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

class ExceptionCacheAnnotationClass
{
    /**
     * @Cache(id="'smbfW5ktNTW0SDllowtvd8JqaCAqtOmpFxVzJd4usibxt5g8vc0ADUSNYxKsxO9AHhdEbdNon2zIrhvNEZq02ZU7tGN2RlHohzPMAEBVMcWqJYaeu21aBQrQGcHP9S1aeXd4rLYvUwBRmkBnRG8V2PeoGbtzzt5roZp3MaPiT9zufolsHePRTprf6sv2vlAQMzQtUlpNIcyqoqDc6RTho1PqddnFaF9mhXi875Mzffru5rVE1234'")
     */
    public function cacheWithTooLongId(): bool
    {
        return false;
    }
}
