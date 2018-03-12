<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Address;
use AppBundle\Entity\Property;
use AppBundle\Form\ContactType;
use AppBundle\Services\Cleaner;
use AppBundle\Services\SearchNarrower;
use AppBundle\Services\Changer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use AppBundle\Form\ContactAddPropertyType;
use Symfony\Component\Form\FormError;
/**
 * Controller that contains methods for anything having to do with a contact.
 * some generated by a CRUD
 */
class ContactController extends Controller
{
    /**
     * story9i
     * Front end for searching for a contact.
     *
     * I have no clue why but DO NOT MOVE THIS TO THE BOTTOM OF THE FILE... if you
     * do, the route breaks, I suspect it has something to do with the crud generated route
     * for viewing a contact.
     *
     * @Route("/contact/search", name="contact_search")
     * @Method("GET")
     */
    public function searchAction()
    {
        // Render the twig with required data
        return $this->render('Contact/searchContact.html.twig', array(
            'viewURL' => '/contact/'
        ));
    }

    /**
     * Lists all contact entities.
     *
     * @Route("/contact/", name="contact_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $contacts = $em->getRepository('AppBundle:Contact')->findAll();

        return $this->render('contact/index.html.twig', array(
            'contacts' => $contacts,
        ));
    }

    /**
     * Creates a new contact entity.
     *
     * @Route("/contact/new", name="contact_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm('AppBundle\Form\ContactType', $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            return $this->redirectToRoute('contact_show', array('id' => $contact->getId()));
        }

        return $this->render('Contact/new.html.twig', array(
            'contact' => $contact,
            'form' => $form->createView()
        ));
    }

    /**
     * Finds and displays a contact entity.
     *
     * @Route("/contact/{id}", name="contact_show")
     * @Method("GET")
     */
    public function showAction(Contact $contact)
    {
        $deleteForm = $this->createDeleteForm($contact);

        $addPropertyForm = $this->createForm(ContactAddPropertyType::class,null,array('contact'=>$contact->getId()));

        return $this->render('contact/show.html.twig', array(
            'contact' => $contact,
            'delete_form' => $deleteForm->createView(),
            'add_property_form' => $addPropertyForm->createView()
        ));
    }

    /**
     * Displays a form to edit an existing contact entity.
     *
     * @Route("/contact/{id}/edit", name="contact_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Contact $contact)
    {
        $deleteForm = $this->createDeleteForm($contact);
        $editForm = $this->createForm('AppBundle\Form\ContactType', $contact);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contact_show', array('id' => $contact->getId()));
        }

        return $this->render('contact/edit.html.twig', array(
            'contact' => $contact,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a contact entity.
     *
     * @Route("/contact/{id}", name="contact_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Contact $contact)
    {
        $form = $this->createDeleteForm($contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($contact);
            $em->flush();
        }

        return $this->redirectToRoute('contact_index');
    }

    /**
     * Creates a form to delete a contact entity.
     *
     * @param Contact $contact The contact entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Contact $contact)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('contact_delete', array('id' => $contact->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * Lists all contactSearch entities.
     *
     * @Route("/contact/jsonsearch/", name="contact_jsonsearch_empty")
     * @Route("/contact/jsonsearch/{searchQuery}", name="contact_jsonsearch")
     * @Method("GET")
     */
    public function jsonSearchAction($searchQuery = "")
    {
        // Clean the input
        $searchQuery = htmlentities($searchQuery);

        // if the string to query on is less than or equal to 100 characters
        if(strlen($searchQuery) <= 100 && !empty($searchQuery))
        {
            // create a cleaner to cleanse the search query
            $cleaner = new Cleaner();

            // cleanse the query
            $cleanQuery = $cleaner->cleanSearchQuery($searchQuery);

            // get an entity manager
            $em = $this->getDoctrine()->getManager();

            // Use the repository to query for the records we want.
            // Store those records into an array.
            $contactSearches = $em->getRepository(Contact::class)->contactSearch($cleanQuery);

            // create a SearchNarrower to narrow down our searches
            $searchNarrower = new SearchNarrower();

            // narrow down our searches, and store their values along side their field values
            $searchedData = $searchNarrower->narrower($contactSearches, $cleanQuery, new Contact());

            // look in the array of narrowed searches/values for the first element (this will be the array of narrowed searches)
            //$narrowedResults = $searchedData[0];

            // Return the results as a json object
            // NOTE: Serializer service needs to be enabled for this to work properly
            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            // Don't display the 'properties' data or the 'address' data as JSON. Makes it more human readable.
            $normalizer->setIgnoredAttributes(array("properties", "address"));
            $serializer = new Serializer(array($normalizer), array($encoder));

            return JsonResponse::fromJsonString($serializer->serialize($searchedData, 'json'));

        }

        // string over 100, return empty array.
        return $this->json(array());
    }

    /**
     * Story 4k
     * Handles the associating of a property onto a contact
     *
     * @param Request $request
     * @Route("/contact/addpropertytocontact", name="add_property_to_contact")
     * @method("POST")
     */
    public function addPropertyAction(Request $request)
    {
        //if the form is posted
        if($request->getMethod() == 'POST')
        {
            $em = $this->getDoctrine()->getManager();
            $contactRepo = $em->getRepository(Contact::class);

            $contact = $contactRepo->findOneById($request->request->get('appbundle_propertyToContact')['contact']);
            $property = $em->getRepository(Property::class)->findOneById($request->request->get('appbundle_propertyToContact')['property']);

            if($contact != null && $property != null)
            {

                //$addPropertyForm = $this->createForm(ContactAddPropertyType::class,null,array('contact'=>$contact->getId()));

                //$addPropertyForm->addError(new FormError("Error msg"));

                if(in_array($property, $contact->getProperties()->toArray()))
                {
                    // TODO error message
                }
                else
                {
                    $properties = $contact->getProperties();
                    $properties->add($property);
                    $contact->setProperties($properties);


                    $contactRepo->save($contact);


                    return $this->redirectToRoute("contact_show", array("id"=>$contact->getId()));
                }
            }
        }




        //If there wasn't a success anywhere, redirect to the contact search page
        return $this->redirectToRoute("contact_search");
    }

    /**
     * Story 4k
     * Handles the removal of an associated property from a contact
     * @param Request $request
     * @Route("/contact/removepropertyfromcontact", name="remove_property_from_contact")
     * @method("POST")
     */
    public function removePropertyAction(Request $request)
    {
        //if the form is posted
        if($request->getMethod() == 'POST')
        {
            $em = $this->getDoctrine()->getManager();
            $contactRepo = $em->getRepository(Contact::class);

            $contact = $contactRepo->findOneById($request->request->get('appbundle_propertyToContact')['contact']);
            $property = $em->getRepository(Property::class)->findOneById($request->request->get('appbundle_propertyToContact')['property']);

            if($contact != null && $property != null)
            {
                if(!in_array($property, $contact->getProperties()->toArray()))
                {
                    $properties = $contact->getProperties();
                    $properties->removeElement($property);
                    $contact->setProperties($properties);

                    $contactRepo->save($contact);

                    return $this->redirectToRoute("contact_show", array("id"=>$contact->getId()));
                }
            }
        }

        //If there wasn't a success anywhere, redirect to the contact search page
        return $this->redirectToRoute("contact_search");
    }
}