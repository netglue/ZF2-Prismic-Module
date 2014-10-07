<?php

namespace NetgluePrismic;

use Prismic\Api;
use Prismic\Ref;
use Prismic\Document;

use NetgluePrismic\Exception;
use NetgluePrismic\ApiAwareTrait;

class Context implements ApiAwareInterface
{

    use ApiAwareTrait;

    /**
     * Prismic Ref Instance
     * @var Ref|NULL
     */
    protected $ref;

    /**
     * Set the Prismic Ref
     * @param Ref $ref
     * @return void
     */
    public function setRef(Ref $ref)
    {
        $this->ref = $ref;
    }

    /**
     * Return Current context/ref
     * @return Ref
     */
    public function getRef()
    {
        if(!$this->ref) {
            $this->setRef($this->getMasterRef());
        }

        return $this->ref;
    }

    /**
     * Return the master Ref
     * @return Ref
     */
    public function getMasterRef()
    {
        return $this->getPrismicApi()->master();
    }

    public function getRefWithString($refId)
    {
        return current(array_filter($this->getPrismicApi()->refs(), function($item) use ($refId) {
            return $item->getRef() === $refId;
        }));
    }

    /**
     * Return the string ref for the selected context
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getRef();
    }

    /**
     * Return a single document with the given id at the current repo ref
     * @param string $id
     * @return Document|NULL
     */
    public function getDocumentById($id)
    {
        $query = sprintf('[[:d = at(document.id, "%s")]]', $id);
        $api = $this->getPrismicApi();
        $documents = $api->forms()->everything->query($query)->ref((string) $this->getRef())->submit();
        if(count($documents->getResults())) {
            // There should be only one!
            return current($documents->getResults());
        }

        return NULL;
    }

    /**
     * Return a single document for the given bookmark name
     * @param string $bookmark
     * @return Document
     * @throws Exception\RuntimeException
     */
    public function getDocumentByBookmark($bookmark)
    {
        /**
         * Either a string id or NULL
         */
        $documentId = $this->getPrismicApi()->bookmark($bookmark);
        if(!$documentId) {
            throw new Exception\RuntimeException(sprintf(
                'The bookmark %s does not exist in this repository',
                (string) $bookmark
            ));
        }
        $document = $this->getDocumentById($documentId);

        if(!$document) {
            throw new Exception\RuntimeException(sprintf(
                'No document could be found with the id %s referenced by the bookmark %s',
                $documentId,
                $bookmark
            ));
        }

        return $document;
    }

    /**
     * Whether the given document has been bookmarked
     * @param Document|string $doc Either a Document instance or the ID of a document
     * @return bool
     */
    public function isBookmarked($doc)
    {
        $name = $this->findBookmarkByDocument($doc);
        return !empty($name);
    }

    /**
     * Return the bookmark name for the given document
     * @param Document|string $doc Either a Document instance or the ID of a document
     * @return string|NULL
     * @throws Exception\InvalidArgumentException
     */
    public function findBookmarkByDocument($doc)
    {
        if(!$doc instanceof Document) {
            if(!$d = $this->getDocumentById($doc)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected a document instance or a valid document id. Received %s %s',
                    gettype($doc),
                    (is_scalar($doc) ? $doc : '')
                ));
            }
            $doc = $d;
        }
        $id = $doc->getId();
        foreach($this->getPrismicApi()->bookmarks() as $name => $target) {
            if($id === $target) {
                return $name;
            }
        }

        return NULL;
    }



}
