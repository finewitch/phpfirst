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


class FilterProducts
{
    public function getAllProducts($request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $data = json_decode($request->getContent(), true);


        $request = Request::createFromGlobals();

        $credential = new PrivateAppCredential('e34e44297a9aec24a869b64147e0b17e', 'f17acfadd7a7528e00c70690e6fd452e', 'b868d483e4667c0118e358ef10e73fb5');
        $client  = new Client($credential, 'textil-one-dev.myshopify.com', [
            'metaCacheDir' => './tmp' // Metadata cache dir, required
        ]);

        $products = $client->getProductManager()->findAll([

            'product_type' => 'Caps & Mützen'
            // $data['produkt_type'],
            
        ]); 
        $customerId = ['customer_id' => $data['customer_id']];
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
        $wizardData= array_merge($smallProducts, $customerId);

        //RESPONSE
        
        $filteredProducts = $serializer->serialize($wizardData, "json");

        return $filteredProducts;


        // return $response;
        


        // {
        //     "0": {
        //         "id": 1816216830009,
        //         "title": "Basic Snapback Monochrome",
        //         "handle": "basic-snapback-monochrome",
        //         "image": "https://cdn.shopify.com/s/files/1/0130/7181/0617/products/CB610_0033.jpg?v=1541612224",
        //         "price": "2.73"
        //     },
        //     "1": {
        //         "id": 1222755942457,
        //         "title": "Beechfield Snapback Trucker",
        //         "handle": "beechfield-snapback-trucker",
        //         "image": "https://cdn.shopify.com/s/files/1/0130/7181/0617/products/cb640_bright-royal_white_653f63cc-cc69-4771-8dae-2c3521e6edd8.jpg?v=1542285033",
        //         "price": "3.33"
        //     },
        //     "customer_id": "915908919353"
        // }

    }
}