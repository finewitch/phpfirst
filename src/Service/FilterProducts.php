<?php
namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class FilterProducts
{
    public function getAllProducts(Request $request)
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

        return $response;

    }
}