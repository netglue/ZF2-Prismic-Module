<?php

namespace NetgluePrismic;

use Prismic\Api;
use Prismic\Ref;
use Prismic\Document;

class Context implements ApiAwareInterface
{

    use ApiAwareTrait;

    /**
     * Prismic Ref Instance
     * @var Ref|NULL
     */
    protected $ref;

    /**
     * Whether the current request indicates there is privileged access
     * @var bool
     */
    protected $privileged = false;

    /**
     * Set the Prismic Ref
     * @param  Ref  $ref
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
        if (!$this->ref) {
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

    /**
     * Given a string repository ref, return the corresponding Ref object
     * @param  string   $refId
     * @return Ref|null Ref or null if the id is not valid or does not exist
     */
    public function getRefWithString($refId)
    {
        return current(array_filter($this->getPrismicApi()->refs(), function ($item) use ($refId) {
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
     * @param  string        $id
     * @return Document|NULL
     */
    public function getDocumentById($id)
    {
        $query = sprintf('[[:d = at(document.id, "%s")]]', $id);
        $api = $this->getPrismicApi();
        $documents = $api->forms()->everything->query($query)->ref((string) $this->getRef())->submit();
        if (count($documents->getResults())) {
            // There should be only one!
            return current($documents->getResults());
        }

        return NULL;
    }

    /**
     * Return a single document for the given bookmark name
     * @param  string                     $bookmark
     * @return Document
     * @throws Exception\RuntimeException
     */
    public function getDocumentByBookmark($bookmark)
    {
        /**
         * Either a string id or NULL
         */
        $documentId = $this->getPrismicApi()->bookmark($bookmark);
        if (!$documentId) {

            throw new Exception\RuntimeException(sprintf(
                'The bookmark %s does not exist in this repository or has not been linked to a document',
                (string) $bookmark
            ));
        }

        return $this->getDocumentById($documentId);
    }

    /**
     * Whether the given document has been bookmarked
     * @param  Document|string $doc Either a Document instance or the ID of a document
     * @return bool
     */
    public function isBookmarked($doc)
    {

        try {
            $name = $this->findBookmarkByDocument($doc);
        } catch(Exception\DocumentNotFoundException $exception) {
            $name = null;
        }

        return !empty($name);
    }

    /**
     * Return the bookmark name for the given document
     * @param  Document|string                    $doc Either a Document instance or the ID of a document
     * @return string|NULL
     * @throws Exception\InvalidArgumentException
     */
    public function findBookmarkByDocument($doc)
    {
        if (!$doc instanceof Document) {
            if (!$d = $this->getDocumentById($doc)) {
                throw new Exception\DocumentNotFoundException(sprintf(
                    'Expected a document instance or a valid document id. Received %s %s',
                    gettype($doc),
                    (is_scalar($doc) ? $doc : '')
                ));
            }
            $doc = $d;
        }
        $id = $doc->getId();
        foreach ($this->getPrismicApi()->bookmarks() as $name => $target) {
            if ($id === $target) {
                return $name;
            }
        }

        return NULL;
    }

    /**
     * Set the privileged access flag
     * @param  bool $flag
     * @return void
     */
    public function setPrivilegedAccess($flag = false)
    {
        $this->privileged = (bool) $flag;
    }

    /**
     * Whether privileged access is allowed
     * @return bool
     */
    public function getPrivilegedAccess()
    {
        return $this->privileged;
    }

}
