<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractController
{
    /* Default status code for responses */
    protected $statusCode = 200;

    /* Get the current status code */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /* Set the status code
     * Params:
     *   - $statusCode: The HTTP status code to set
     * Returns:
     *   - $this: The current instance for method chaining
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /* General method for creating a JSON response
     * Params:
     *   - $data: The data to include in the response
     *   - $message: The message to include in the response
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function response($data, $message)
    {
        $responseData = [
            "message" => $message,
            'status' => $this->getStatusCode(),
            'data' => $data,
        ];
        return new JsonResponse($responseData, $this->getStatusCode());
    }

    /* Respond with errors
     * Params:
     *   - $errors: Array of error messages
     *   - $headers: Additional headers for the response (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondWithErrors($errors, $headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'message' => $errors,
        ];
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /* Respond with success
     * Params:
     *   - $success: Success message or data
     *   - $headers: Additional headers for the response (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondWithSuccess($success, $headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'success' => $success,
        ];
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /* Respond with unauthorized error
     * Params:
     *   - $message: Custom error message (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondUnauthorized($message = 'Not authorized!')
    {
        return $this->setStatusCode(401)->respondWithErrors($message);
    }

    /* Respond with validation error
     * Params:
     *   - $errors: Array|string: Les messages d'erreur de validation
     * Returns:
     *   - JsonResponse: L'objet de réponse JSON
     */
    public function respondValidationError($errors)
{
    if (!is_array($errors)) {
        // Si les erreurs ne sont pas déjà un tableau, convertissez-les en un tableau avec une seule entrée
        $errors = [$errors];
    }

    $data = [
        'status' => $this->getStatusCode(),
        'errors' => $errors,
    ];

    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
}

    /* Respond with not found error
     * Params:
     *   - $message: Custom error message (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondNotFound($message = 'Not found!')
    {
        return $this->setStatusCode(404)->respondWithErrors($message);
    }

    /* Respond with conflict error
     * Params:
     *   - $message: Custom error message (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondConflict($message = 'Conflict!')
    {
        return $this->setStatusCode(409)->respondWithErrors($message);
    }

    /* Respond with bad request error
     * Params:
     *   - $message: Custom error message (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondBadRequest($message = 'Bad Request')
    {
        return $this->setStatusCode(400)->respondWithErrors($message);
    }

    /* Respond with failed dependency error
     * Params:
     *   - $message: Custom error message (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondFailedDependency($message = 'Failed Dependency')
    {
        return $this->setStatusCode(424)->respondWithErrors($message);
    }

    /* Respond with created status and optional data
     * Params:
     *   - $data: The data to include in the response (optional)
     * Returns:
     *   - JsonResponse: The JSON response object
     */
    public function respondCreated($data, $headers)
    {
        return $this->setStatusCode(201)->response($data, $headers);
    }

    /* Transform request body from JSON
     * Params:
     *   - $request: The Symfony HTTP request object
     * Returns:
     *   - $request: The transformed request object
     */
    public function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

    /* Generate a random token
     * Returns:
     *   - string: The generated random token
     */
    function generateToken()
    {
        $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < 200; ++$i) {
            $pieces[] = $stringSpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
