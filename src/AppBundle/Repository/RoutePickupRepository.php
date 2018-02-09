<?php

namespace AppBundle\Repository;

/**
 * RoutePickupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */

use AppBundle\Entity\RoutePickup;

class RoutePickupRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * Story 22b
     * @param RoutePickup $routePickup RoutePickup to be inserted
     * @return integer the ID of the inserted route pickup
     */
    public function save(RoutePickup $routePickup){
        $em = $this->getEntityManager();
        // persist the new contact in the database
        $em->persist($routePickup);
        // flush them to the database
        $em->flush();
        //Close the entity manager
        // return the id of the new contact in the database
        return $routePickup->getId();
    }

    /**
     * Story 22b
     * @param mixed $routeId The route id of the route that is being updated
     * @param mixed $startAt the pickup order of the routePickups to start at (inclusive)
     * @param mixed $increment Whether to increment or decrement
     */
    public function updateOrders($routeId, $startAt, $increment=true){

        $em = $this->getEntityManager();

        $query = $em->createQuery("UPDATE AppBundle\Entity\RoutePickup p SET p.pickupOrder = p.pickupOrder + :increment WHERE IDENTITY(p.route) = :routeId AND p.pickupOrder >= :startAt")
            ->setParameter('routeId',$routeId)
            ->setParameter('startAt',$startAt)
            ->setParameter('increment', $increment ? 1 : -1);

        return $query->execute();
    }

    /**
     * Story 22a
     * @param RoutePickup $routePickup RoutePickup to be removed
     */
    public function remove(RoutePickup $routePickup){
        $em = $this->getEntityManager();
        // persist the new contact in the database
        $em->remove($routePickup);
        // flush them from the database
        $em->flush();
        //Close the entity manager
        $em->close();
    }
}
