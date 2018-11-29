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



    public function filterProducts($products, $ProductBranch, $ProductTag, $extraParam, $returnItems = 3, $ignoreProducts = []){
        
        $smallProducts=[];

        if($extraParam === 'branch'){

            foreach ($products as $value) {
            
                $tags = $value->getTags();
    
                if(strpos($tags, $ProductBranch) !== false){

                    if(in_array($value->getId(), $ignoreProducts)) {

                        continue;

                    }

                    $product  = [
                        'variant_id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'handle' => $value->getHandle(),
                        'image' => $value->getImage()->getSrc(),
                        'price' => $value->getVariants()[0]->getPrice(),
                        'tags' => $value->getTags(),
                        'quantity' => 1
                    ];
                    $smallProducts[$value->getId()] = $product;
                    //array_push($smallProducts, $product);
                }
    
            }
            // var_dump($smallProducts , '2');


        }else{

            
            foreach ($products as $value) {
                
                $tags = $value->getTags();
                // var_dump($tags);
                // var_dump($value, 'val');
                
                if(strpos($tags, $ProductBranch) !== false && strpos($tags, $ProductTag) !== false){
                    
                    $product = [
                        'variant_id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'handle' => $value->getHandle(),
                        'image' => $value->getImage()->getSrc(),
                        'price' => $value->getVariants()[0]->getPrice(),
                        'tags' => $value->getTags(),
                        'quantity' => 1
                    ];

                    // var_dump($product, 'MATCH');
                    array_push($smallProducts, $product);

                }
                
            }
            // var_dump($smallProducts , '1');
        }
            
        $smallProducts = array_slice($smallProducts, 0,$returnItems);
        return $smallProducts;
    }



    
    public function getAllProducts($request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        //data
        $data = json_decode($request->getContent(), true);
        $ProductType = $data['produkt_type'];
        $ProductBranch = $data['branch'];
        $ProductTag = $data['tag'];
        $customerId = ['customer_id' => $data['customer_id']];


        $request = Request::createFromGlobals();

        $credential = new PrivateAppCredential('e34e44297a9aec24a869b64147e0b17e', 'f17acfadd7a7528e00c70690e6fd452e', 'b868d483e4667c0118e358ef10e73fb5');
        $client  = new Client($credential, 'textil-one-dev.myshopify.com', [
            'metaCacheDir' => './tmp' // Metadata cache dir, required
        ]);

        $products = $client->getProductManager()->findAll([

            'product_type' => $ProductType,
            
        ]); 



        //TRY TO  FIND MATCH WITH BOTH
        $smallProducts = self::filterProducts($products, $ProductBranch, $ProductTag, $extraParam='all');

        if(count($smallProducts) <= 2){
            $ignoreProducts = array_keys($smallProducts);
            //INDLUDE ONLY PRODUKT TYPE AND BRANCH
            $needItems = 3 - count($smallProducts);
            $newSmallProducts = self::filterProducts($products, $ProductBranch, $ProductTag, $extraParam='branch', $needItems, $ignoreProducts);
            $smallProducts = array_merge($smallProducts, $newSmallProducts);    
        }
        // var_dump($smallProducts, '3');
        // var_dump($smallProducts);


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

            
        //$wizardData= array_merge($smallProducts, $customerId);
                $wizardData = [
                    'products' => $smallProducts,
                    'customer_id' => $customerId
                ];
        //RESPONSE
        
        $filteredProducts = $serializer->serialize($wizardData, "json");

        return $filteredProducts;

    }
    
}