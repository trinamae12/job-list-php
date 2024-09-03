<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;
use Framework\Authorization;

class ListingController
{
    protected $db;

    public function __construct() 
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    /**
     * Show all listings
     * 
     * @return void
     */
    public function index() {
        $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC')->fetchAll();

        loadView('listings/index', [
            'listings' => $listings
        ]);
    }

    /**
     * Show create listing form
     * 
     * @return void
     */
    public function create() {
        loadView('listings/create');
    }

    /**
     * Store data into database
     * 
     * @return void
     */
    public function store() {
        $allowedFields = [
            'title',
            'description',
            'salary',
            'tags',
            'company',
            'address',
            'city',
            'state',
            'phone',
            'email',
            'requirements',
            'benefits'
        ];

        // array_intersect_key = create new array where keys match in the two arrays
        // array_flip = flip values to keys and keys to values, used because $allowed Fields are values with numeric keys
        $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

        $newListingData['user_id'] = Session::get('user')['id'];

        // apply sanitize function from helper on each value of the array
        $newListingData = array_map('sanitize', $newListingData);

        $requiredFields = [
            'title',
            'description',
            'email',
            'city',
            'state',
            'salary'
        ];

        $errors = [];

        foreach($requiredFields as $requiredField) {
            if(empty($newListingData[$requiredField]) || !Validation::string($newListingData[$requiredField])) {
                $errors[$requiredField] = ucfirst($requiredField) . " is required.";
            }
        }

        if(!empty($errors)) {
            // Reload view with errors
            loadView('listings/create', [
                'errors' => $errors,
                'listing' => $newListingData
            ]);
        } else {
            // Submit data

            // Only fill out fields that has values
            $fields = [];
            foreach($newListingData as $field => $value) {
                $fields[] = $field;
            }
            $fields = implode(", ", $fields);

            $values = [];
            foreach($newListingData as $field => $value) {
                // Convert empty strings to null
                if($value === '') {
                    $newListingData[$field] = null;
                }
                $values[] = ":" . $field;
            }
            $values = implode(", ", $values);

            $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";

            $this->db->query($query, $newListingData);

            Session::setFlashMessage('success_message', 'Listing created successfully');

            redirect("/listings");
        }
    }

    /**
     * Show listing detail
     * 
     * @param array $params
     * @return void
     */
    public function show($params) {
        // $id = $_GET['id'] ?? '';
        $id = $params['id'] ?? '';

        $params = [
            'id' => $id
        ];
        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exists
        if(!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        loadView('listings/show', [
            'listing' => $listing
        ]);
    }

    /**
     * Delete a job listing
     * 
     * @param array $params
     * @return void
     */
    public function destroy($params) {
        $id = $params['id'] ?? '';

        $params= [
            'id' => $id
        ];

        $listing = $this->db->query("SELECT * FROM listings WHERE id = :id", $params)->fetch();

        // Check if listing exists
        if(!$listing) {
            ErrorController::notFound("Listing not found");
            return;
        }

        // Authorization 
        //inspectAndExit($listing);
        if(!Authorization::isOwner($listing->user_id)) {
            //$_SESSION['error_message'] = 'You are not authorized to delete this listing';
            Session::setFlashMessage('error_message', 'You are not authorized to delete this listing');
            return redirect('/listings/' . $listing->id);
        }

        $this->db->query("DELETE FROM listings WHERE id = :id", $params);

        // Set flash message
        //$_SESSION['success_message'] = 'Listing deleted successfully';
        Session::setFlashMessage('success_message', 'Listing deleted successfully');

        redirect("/listings");
    }

    /**
     * Show edit listing form
     * 
     * @param array $params
     * @return void
     */
    public function edit($params) {
        // $id = $_GET['id'] ?? '';
        $id = $params['id'] ?? '';

        $params = [
            'id' => $id
        ];
        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exists
        if(!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        if(!Authorization::isOwner($listing->user_id)) {
            //$_SESSION['error_message'] = 'You are not authorized to delete this listing';
            Session::setFlashMessage('error_message', 'You are not authorized to edit this listing');
            return redirect('/listings/' . $listing->id);
        }

        loadView('listings/edit', [
            'listing' => $listing
        ]);
    }

    /**
     * Update job listing
     * 
     * @param array $params
     * @return void
     */
    public function update($params) {
        $id = $params['id'] ?? '';

        $params = [
            'id' => $id
        ];
        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exists
        if(!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        // Authorize
        if(!Authorization::isOwner($listing->user_id)) {
            //$_SESSION['error_message'] = 'You are not authorized to delete this listing';
            Session::setFlashMessage('error_message', 'You are not authorized to update this listing');
            return redirect('/listings/' . $listing->id);
        }

        $allowedFields = [
            'title',
            'description',
            'salary',
            'tags',
            'company',
            'address',
            'city',
            'state',
            'phone',
            'email',
            'requirements',
            'benefits'
        ];

        $updatedValues = [];

        // array_intersect_key = create new array where keys match in the two arrays
        // array_flip = flip values to keys and keys to values, used because $allowed Fields are values with numeric keys
        $updatedValues = array_intersect_key($_POST, array_flip($allowedFields));

        // apply sanitize function from helper on each value of the array
        $updatedValues = array_map('sanitize', $updatedValues);

        $requiredFields = [
            'title',
            'description',
            'email',
            'city',
            'state',
            'salary'
        ];

        $errors=[];

        foreach($requiredFields as $requiredField) {
            if(empty($updatedValues[$requiredField]) || !Validation::string($updatedValues[$requiredField])) {
                $errors[$requiredField] = ucfirst($requiredField) . " is required.";
            }
        }

        if(!empty($errors)) {
            loadView('listings/edit',[
                'listing' => $listing,
                'errors' => $errors
            ]);
            exit;
        } else {
            // Submit to database
            $updateFields = [];
            foreach(array_keys($updatedValues) as $field) {
                $updateFields[] = "{$field} = :{$field}";
            }

            $updateFields = implode(", ", $updateFields);

            $updateQuery = "UPDATE listings SET ". $updateFields. " WHERE id = :id";

            $updatedValues['id'] = $id;
            $this->db->query($updateQuery, $updatedValues);

            //$_SESSION['success_message'] = 'Listing updated.';
            Session::setFlashMessage('success_message', 'Listing updated');

            redirect('/listings/' . $id);
        }
    }

    /**
     * Search listings by keywords/location
     * 
     * @return void
     */
    public function search() {
        $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';

        $query = "SELECT * FROM listings WHERE (title LIKE :keywords OR description LIKE :keywords OR tags LIKE :keywords OR company LIKE :keywords) AND (city LIKE :location OR state LIKE :location)";

        $params = [
            'keywords' => "%{$keywords}%",
            'location' => "%{$location}%"
        ];

        $listings = $this->db->query($query, $params)->fetchAll();

        loadView('/listings/index', [
            'listings' => $listings,
            'keywords' => $keywords,
            'location' => $location
        ]);
    }
}
