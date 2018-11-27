<?php
namespace App\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Slince\Shopify\PrivateAppCredential;
use Slince\Shopify\Client;

use GuzzleHttp\Client as ClientGuz;
use GuzzleHttp\Psr7\Response as ResponseGuz;


class SaveMetafields
{
    public function index($responseFilterProducts)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        

        // var_dump($responseFilterProducts);

        //ZAPISYWANIE DO METAFIELDS
        $raw = json_encode(array (
            'order' => 
            array (
              'tags' => ['wizard'],
              'customer' => 
              array (
                'id' => $responseFilterProducts->customer_id->customer_id,
              ),
              'line_items' => $responseFilterProducts->products,
              'total_tax' => 0,
              'currency' => 'EUR',
            ),
        ));
        $data = $serializer->serialize($raw, "json");

        $client = new ClientGuz();

        $urlPOST = 'https://textil-one-dev.myshopify.com/admin/orders.json';

        $request= $client->post($urlPOST, [

            'headers' => ['Content-Type' => 'application/json'],

            'auth' => ['e34e44297a9aec24a869b64147e0b17e', 'f17acfadd7a7528e00c70690e6fd452e'],

            'body' => $raw

        ]);

        $response = new Response($request->getStatusCode());

        return $response;

    }
}