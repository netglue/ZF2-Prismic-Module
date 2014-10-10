<?php

namespace NetgluePrismic;

use Prismic\Api;

interface ApiAwareInterface
{

    /**
     * Set the Prismic Api Instance
     * @param  Api  $api
     * @return void
     */
    public function setPrismicApi(Api $api);

    /**
     * Return Prismic Api instance
     * @return Api|NULL
     */
    public function getPrismicApi();

}
