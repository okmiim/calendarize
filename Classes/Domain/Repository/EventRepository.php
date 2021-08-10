<?php

/**
 * Event repository.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Domain\Model\Dto\Search;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Event repository.
 */
class EventRepository extends AbstractRepository
{
    /**
     * Get the IDs of the given search term.
     *
     * @param Search $search
     *
     * @return array
     */
    public function findBySearch(Search $search)
    {
        $query = $this->createQuery();
        $constraints = [];
        if ($search->getFullText()) {
            $constraints['fullText'] = $query->logicalOr([
                $query->like('title', '%' . $search->getFullText() . '%'),
                $query->like('description', '%' . $search->getFullText() . '%'),
            ]);
        }
        if ($search->getCategory()) {
            $constraints['categories'] = $query->contains('categories', $search->getCategory());
        }
        $query->matching($query->logicalAnd($constraints));
        $rows = $query->execute(true);

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int)$row['uid'];
        }

        return $ids;
    }

    /**
     * @param $importId
     *
     * @return mixed|null
     */
    public function findOneByImportId($importId)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching($query->equals('importId', $importId));
        $result = $query->execute()->toArray();

        return $result[0] ?? null;
    }

    /**
     * Get the right Index ID by the event ID.
     *
     * @param int $uid
     *
     * @return Index|null
     */
    public function findNextIndex(int $uid): ?object
    {
        /** @var Event $event */
        $event = $this->findByUid($uid);

        if (!\is_object($event)) {
            return null;
        }

        /** @var IndexRepository $indexRepository */
        $indexRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(IndexRepository::class);

        try {
            $result = $indexRepository->findByEventTraversing($event, true, false, 1, QueryInterface::ORDER_ASCENDING);
            if (empty($result)) {
                $result = $indexRepository->findByEventTraversing($event, false, true, 1, QueryInterface::ORDER_DESCENDING);
            }
        } catch (\Exception $ex) {
            return null;
        }

        if (empty($result)) {
            return null;
        }

        /** @var Index $index */
        $index = $result[0];

        return $index;
    }
}
