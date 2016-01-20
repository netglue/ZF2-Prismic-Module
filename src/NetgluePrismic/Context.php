<?php

namespace NetgluePrismic;

use Prismic\Api;
use Prismic\Ref;
use Prismic\Document;
use Prismic\Predicates;

class Context implements ApiAwareInterface
{

    use ApiAwareTrait;

    /**
     * Prismic Ref Instance
     * @var Ref|NULL
     */
    protected $ref;

    /**
     * Current Ref as a string
     * @var string|null
     */
    protected $refString;

    /**
     * Whether the current request indicates there is privileged access
     * @var bool
     */
    protected $privileged = false;

    /**
     * Array cache of documents by ID
     * @var array
     */
    protected $byId = array();

    /**
     * Array cache of documents by bookmark
     * @var array
     */
    protected $byBookmark = array();

    /**
     * Array cache of documents by UID and Mask
     * @var array
     */
    protected $byUidAndMask = array();

    /**
     * Set the Prismic Ref
     * @param  Ref  $ref
     * @return void
     */
    public function setRef(Ref $ref)
    {
        $this->ref = $ref;
        $this->refString = $ref->getRef();
    }

    /**
     * Set the current ref as a string (For example when in a preview session)
     * @param string $refString
     * @return void
     */
    public function setRefWithString($refString)
    {
        if(null !== $refString) {
            $refString = (string) $refString;
        }
        $this->refString = $refString;
        $ref = $this->getRefWithString($refString);
        if($ref) {
            $this->setRef($ref);
        }
    }

    /**
     * Return Current context/ref
     * @return Ref
     */
    public function getRef()
    {
        if (!$this->ref) {
            return $this->getMasterRef();
        }

        return $this->ref;
    }

    /**
     * Return the current ref as a string
     *
     * If a string ref has been set, this will be returned in favour of a valid API ref so that we can preview releases
     * @return string
     */
    public function getRefAsString()
    {
        if(!empty($this->refString)) {
            return $this->refString;
        }

        return (string) $this->getRef();
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
        return $this->getRefAsString();
    }

    /**
     * Return a single document with the given id at the current repo ref
     * @param  string        $id
     * @return Document|null
     */
    public function getDocumentById($id)
    {
        if (isset($this->byId[$id])) {
            return $this->byId[$id];
        }
        $query = sprintf('[[:d = at(document.id, "%s")]]', $id);
        $api = $this->getPrismicApi();
        $documents = $api->forms()->everything->query($query)->ref($this->getRefAsString())->submit();
        if (count($documents->getResults())) {
            // There should be only one!
            $this->byId[$id] = current($documents->getResults());
            return $this->byId[$id];
        }

        return null;
    }

    public function getDocumentByUidAndMask($uid, $mask)
    {
        if (!isset($this->byUidAndMask[$mask])) {
            $this->byUidAndMask[$mask] = array();
        }
        if (isset($this->byUidAndMask[$mask][$uid])) {
            return $this->byUidAndMask[$mask][$uid];
        }
        $query = [
            Predicates::at("document.type", $mask),
            Predicates::at("my.{$mask}.uid", $uid)
        ];
        $api = $this->getPrismicApi();
        $documents = $api->forms()->everything->query($query)->ref($this->getRefAsString())->submit();
        if (count($documents->getResults())) {
            // There should be only one!
            $this->byUidAndMask[$mask][$uid] = current($documents->getResults());
            return $this->byUidAndMask[$mask][$uid];
        }

        return null;
    }

    /**
     * Return a single document for the given bookmark name
     * @param  string                     $bookmark
     * @return Document
     * @throws Exception\RuntimeException
     */
    public function getDocumentByBookmark($bookmark)
    {
        if (isset($this->byBookmark[$bookmark])) {
            return $this->byBookmark[$bookmark];
        }
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

        $this->byBookmark[$bookmark] = $this->getDocumentById($documentId);

        return $this->byBookmark[$bookmark];
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
