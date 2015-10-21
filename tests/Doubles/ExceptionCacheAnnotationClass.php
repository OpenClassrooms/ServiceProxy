<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ExceptionCacheAnnotationClass
{
    /**
     * @Cache(id="'smbfW5ktNTW0SDllowtvd8JqaCAqtOmpFxVzJd4usibxt5g8vc0ADUSNYxKsxO9AHhdEbdNon2zIrhvNEZq02ZU7tGN2RlHohzPMAEBVMcWqJYaeu21aBQrQGcHP9S1aeXd4rLYvUwBRmkBnRG8V2PeoGbtzzt5roZp3MaPiT9zufolsHePRTprf6sv2vlAQMzQtUlpNIcyqoqDc6RTho1PqddnFaF9mhXi875Mzffru5rVE1234'")
     *
     * @return string
     */
    public function cacheWithTooLongId()
    {
        return false;
    }
}
