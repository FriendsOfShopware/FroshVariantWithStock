<?php declare(strict_types=1);

namespace Frosh\VariantWithStock\Subscriber;

use Shopware\Core\Content\Product\SalesChannel\Detail\Event\ResolveVariantIdEvent;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VariantResolveSubscriber implements EventSubscriberInterface
{
    /**
     * @param SalesChannelRepository<SalesChannelProductCollection> $productRepository
     */
    public function __construct(
        private readonly SalesChannelRepository $productRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ResolveVariantIdEvent::class => 'onResolveVariant',
        ];
    }

    public function onResolveVariant(ResolveVariantIdEvent $event): void
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $event->getProductId()))
            ->addSorting(new FieldSorting('product.available', FieldSorting::DESCENDING))
            ->addSorting(new FieldSorting('product.availableStock', FieldSorting::DESCENDING))
            ->setLimit(1)
        ;

        $event->setResolvedVariantId($this->productRepository->searchIds(
            $criteria,
            $event->getSalesChannelContext(),
        )->firstId());
    }
}
