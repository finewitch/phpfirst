<?php

namespace App\Controller;

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


class ProductController extends AbstractController
{

    /**
     * @Route("/product", name="product")
     */

    public function index(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        
        $serializer = new Serializer($normalizers, $encoders);

        $data = json_decode($request->getContent(), true);

        $request = Request::createFromGlobals();

        // var_dump($data['produkt_type']);


        //api,pass,secret
        $credential = new PrivateAppCredential('e34e44297a9aec24a869b64147e0b17e', 'f17acfadd7a7528e00c70690e6fd452e', 'b868d483e4667c0118e358ef10e73fb5');

        $client  = new Client($credential, 'textil-one-dev.myshopify.com', [
            'metaCacheDir' => './tmp' // Metadata cache dir, required
        ]);

        $products = $client->getProductManager()->findAll([

            // 'product_type' => 'Caps & Mützen'
            $data['produkt_type'],
            
        ]);


        $smallProducts = [];
        
        
        foreach ($products as $value) {
            
            $smallProducts[] = [
                'id' => $value->getId(),
                'title' => $value->getTitle(),
                'handle' => $value->getHandle(),
                'image' => $value->getImage()->getSrc(),
                'price' => $value->getVariants()[0]->getPrice()
            ];

        } 

        //RESPONSE
        
        $filteredProducts = $serializer->serialize($smallProducts, "json");
        

        $response = new Response(
            $filteredProducts,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
        
        //ZAPISYWANIE DO METAFIELDS
        $raw = json_encode(array (
            'order' => 
            array (
              'tags' => 'wizard',
              'customer' => 
              array (
                'id' => 915908919353,
              ),
              'line_items' => 
              array (
                0 => 
                array (
                  'id' => 1816216830009,
                  'title' => 'Basic Snapback Monochrome',
                  'price' => 0,
                  'properties' => 
                  array (
                    'id' => 1816216830009,
                    'title' => 'Basic Snapback Monochrome',
                    'handle' => 'basic-snapback-monochrome',
                    'image' => 'https://cdn.shopify.com/s/files/1/0130/7181/0617/products/CB610_0033.jpg?v=1541612224',
                    'price' => '2.73',
                  ),
                ),
              ),
              'total_tax' => 0,
              'currency' => 'EUR',
            ),
        ));
        $data = $serializer->serialize($raw, "json");
        // var_dump($data);

        $client = new ClientGuz();

        $urlPOST = 'https://textil-one-dev.myshopify.com/admin/orders.json';

        $request1= $client->post($urlPOST, [

            'headers' => ['Content-Type' => 'application/json'],

            'auth' => ['e34e44297a9aec24a869b64147e0b17e', 'f17acfadd7a7528e00c70690e6fd452e'],

            'body' => $raw

        ]);

        // var_dump($response->getStatusCode());

        $response = new Response($request1->getBody());

        // $response = new Response ($products);
        // $response->send(); 

        // dump($jsonContent);die;

        // print_r($products);die;

        return $response;

    }
}
