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



    public function filterProducts($products, $branch, $tag, $extraParam){
        
        $smallProducts=[];

        if($extraParam === 'branch'){

            foreach ($products as $value) {
            
                $tags = $value->getTags();
    
                if(strpos($tags, $branch) !== false){
                    
                    $product  = [
                        'id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'handle' => $value->getHandle(),
                        'image' => $value->getImage()->getSrc(),
                        'price' => $value->getVariants()[0]->getPrice(),
                        'tags' => $value->getTags()
                    ];
                    array_push($smallProducts, $product);
                }
    
            }

        }else{

            
            foreach ($products as $value) {
                
                $tags = $value->getTags();
                
                if(strpos($tags, $branch) !== false && strpos($tags, $tag) !== false){
                    
                    $smallProducts[] = [
                        'id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'handle' => $value->getHandle(),
                        'image' => $value->getImage()->getSrc(),
                        'price' => $value->getVariants()[0]->getPrice(),
                        'tags' => $value->getTags()
                    ];
                }
                
            }
        }
            
        $smallProducts = array_slice($smallProducts, 0,3);
            
        return $smallProducts;
    }



    
    public function getAllProducts($request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $data = json_decode($request->getContent(), true);
        // $ProductType = $data['produkt_type'];
        // $ProductBranch = $data['branch'];


        $request = Request::createFromGlobals();

        $credential = new PrivateAppCredential('e34e44297a9aec24a869b64147e0b17e', 'f17acfadd7a7528e00c70690e6fd452e', 'b868d483e4667c0118e358ef10e73fb5');
        $client  = new Client($credential, 'textil-one-dev.myshopify.com', [
            'metaCacheDir' => './tmp' // Metadata cache dir, required
        ]);

        $products = $client->getProductManager()->findAll([

            'product_type' => 'Caps & Mützen',
            
        ]); 

        $customerId = ['customer_id' => $data['customer_id']];


        $branch = 'Gastronomie';
        $tag = 'test';


        //TRY TO  FIND MATCH WITH BOTH
        $smallProducts = self::filterProducts($products, $branch, $tag, $extraParam='all');

        if(count($smallProducts) <= 2){
            //INDLUDE ONLY PRODUKT TYPE AND BRANCH
            $smallProducts = self::filterProducts($products, $branch, $tag, $extraParam='branch');

        }


            // foreach($smallProducts as $product){
            //     array_unique($product);
            // }
            // var_dump($smallProducts, 'ARAJKA');
            // // $arr2 = array_unique($smallProducts, SORT_REGULAR);
            // foreach($smallProducts as $key=>$value){
            //     if(!empty($id) && in_array($value['id'],$id)) unset($smallProducts[$key]);  //unset from $array if username already exists
            //         $id[] = $value['id']; 
            // }

            // var_dump($smallProducts, 'ARAJKA DWA');

            
        $wizardData= array_merge($smallProducts, $customerId);

        //RESPONSE
        
        $filteredProducts = $serializer->serialize($wizardData, "json");

        return $filteredProducts;

    }
    
}