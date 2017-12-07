<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Address;
use AppBundle\Form\ContactType;
use AppBundle\Services\Cleaner;
use AppBundle\Services\SearchNarrower;
use AppBundle\Services\Changer;

/**
 * Controller that contains methods for anything having to do with a contact.
 */
class ContactController extends Controller
{
    /**
     * Lists all contact entities.
     *
     * @Route("/", name="contact_index")
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
     * @Route("/new", name="contact_new")
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
     * @Route("/{id}", name="contact_show")
     * @Method("GET")
     */
    public function showAction(Contact $contact)
    {
        $deleteForm = $this->createDeleteForm($contact);

        return $this->render('contact/show.html.twig', array(
            'contact' => $contact,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing contact entity.
     *
     * @Route("/{id}/edit", name="contact_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Contact $contact)
    {
        $deleteForm = $this->createDeleteForm($contact);
        $editForm = $this->createForm('AppBundle\Form\ContactType', $contact);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contact_show');
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
     * @Route("/{id}", name="contact_delete")
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
     * @Route("/contact/search/{searchQuery}", name="contact_search")
     * @Method("GET")
     */
    public function searchAction($searchQuery)
    {
        // if the string to query onn is less than or equal to 100 characters
        if(strlen($searchQuery) <= 100)
        {
            // create a cleaner to cleanse the search query
            $cleaner = new Cleaner();

            // cleanse the query
            $cleanQuery = $cleaner->cleanSearchQuery($searchQuery);

            var_dump($cleanQuery);

            // get an entity manager
            $em = $this->getDoctrine()->getManager();

            // Use the repository to query for the records we want.
            // Store those records into an array.
            $contactSearches = $em->getRepository(Contact::class)->contactSearch($cleanQuery);

            // create a SearchNarrower to narrow down our searches
            $searchNarrower = new SearchNarrower();

            // narrow down our searches, and store their values along side their field values
            $searchedData = $searchNarrower->narrowContacts($contactSearches, $cleanQuery);

            // look in the array of narrowed searches/values for the first element (this will be the array of narrowed searches)
            $narrowedResults = $searchedData[0];

            // create a Changer to convert the narrowed searches to JSON format
            $changer = new Changer();

            // An open square bracket to denote the start of the JSON object string
            $jsonEncodedSearches = "[";

            // a counter to index into the array of narrowed results's data
            $i = 0;

            // foreach record in the array of narrowed results
            foreach ($narrowedResults as $result)
            {
                // append the converted entity JSON string to the string we created above.
                // the '$searchedData[1][$i]' is indexing into the current records field values
                $jsonEncodedSearches .= $changer->ToJSON($result, $searchedData[1][$i]);

                // increment our counter
                $i++;
            }

            // chop off the last comma at the end of the JSON string
            $jsonEncodedSearches = substr($jsonEncodedSearches,0,strlen($jsonEncodedSearches)-1);

            // if the length of the JSON string is greater than 0
            if(strlen($jsonEncodedSearches) > 0)
            {
                // close the square bracket (this is the end of the JSON object string)
                $jsonEncodedSearches .= "]";

                // render the page passing to it the records returned from the query, after being converted to JSON format.
                return $this->render('contactsearch/contactJSONSearches.html.twig', array(
                    'contactSearches' => $jsonEncodedSearches,
                ));
            }
        }

        // Display a blank JSON object, the system will interpret this as nothing being returned
        return $this->render('contactsearch/contactJSONSearches.html.twig', array(
                'contactSearches' => '[{"role":null}]',
            ));
    }
}

