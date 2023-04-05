<?php
   function is_promo($card_set_id)
   {
      // Spécificité carte promo
      switch ($card_set_id)
      {
         case 'basep': 	{ return true; }
         case 'np':    	{ return true; }
         case 'dpp':   	{ return true; }
         case 'hsp':   	{ return true; }
         case 'bwp':   	{ return true; }
         case 'xyp':   	{ return true; }
         case 'smp':   	{ return true; }
         case 'swshp': 	{ return true; }
         case 'svp': 	{ return true; }
         case 'np': 		{ return true; }
         case 'mcd11': 	{ return true; }
         case 'mcd12': 	{ return true; }
         case 'mcd13': 	{ return true; }
         case 'mcd14': 	{ return true; }
         case 'mcd15': 	{ return true; }
         case 'mcd16': 	{ return true; }
         case 'mcd17': 	{ return true; }
         case 'mcd18': 	{ return true; }
         case 'mcd19': 	{ return true; }
         case 'mcd20': 	{ return true; }
         case 'mcd21': 	{ return true; }
         case 'mcd22':	{ return true; }
         case 'mcd23': 	{ return true; }
         case 'mcd24': 	{ return true; }
         case 'mcd25': 	{ return true; }
      }
      
      return false;
   }

   function get_rarity_data($rarity)
   {
      $itemJSON_Card = array();
      
      switch (strtolower(@$rarity))
      {
         case '':
         {
            $itemJSON_Card['rarityIndex'] = NULL;
            $itemJSON_Card['raritySimplified'] = NULL;
            break;
         }	
            
         case strtolower('promo'):
         {
            $itemJSON_Card['rarityIndex'] = 0;
            $itemJSON_Card['raritySimplified'] = 'promo';
            break;
         }

         case strtolower('common'):
         {
            $itemJSON_Card['rarityIndex'] = 1;
            $itemJSON_Card['raritySimplified'] = 'common';
            break;
         }

         case strtolower('uncommon'):
         {
            $itemJSON_Card['rarityIndex'] = 2;
            $itemJSON_Card['raritySimplified'] = 'uncommon';
            break;
         }

         case strtolower('rare'):
         {
            $itemJSON_Card['rarityIndex'] = 3;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('rare holo'):
         {
            $itemJSON_Card['rarityIndex'] = 3;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('rare holo ex'):
         {
            $itemJSON_Card['rarityIndex'] = 3;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('rare ace'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('Rare Holo Star'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('Rare Prism Star'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }							

         case strtolower('Rare Holo LV.X'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }	
            
         case strtolower('classic collection'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }	

         case strtolower('Rare Shiny'):
         case strtolower('Rare Shining'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }	

         case strtolower('Amazing Rare'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('Radiant Rare'):
         {
            $itemJSON_Card['rarityIndex'] = 4;
            $itemJSON_Card['raritySimplified'] = 'rare';
            break;
         }

         case strtolower('Rare BREAK'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	

         case strtolower('rare ultra'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	
         
         case strtolower('Rare Holo V'):
         case strtolower('v'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }		

         case strtolower('Rare Prime'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	
            
         case strtolower('Double Rare'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	

         case strtolower('Rare Holo GX'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	

         case strtolower('Rare Shiny GX'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	

         case strtolower('LEGEND'):
         {
            $itemJSON_Card['rarityIndex'] = 5;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }	

         case strtolower('vm'): // V-MAX
         case strtolower('Rare Holo VMAX'): // V-MAX
         {
            $itemJSON_Card['rarityIndex'] = 6;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }

         case strtolower('Rare Holo VSTAR'): // V-MAX
         {
            $itemJSON_Card['rarityIndex'] = 6;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }

         case strtolower('Hyper Rare'): 
         {
            $itemJSON_Card['rarityIndex'] = 6;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }

         case strtolower('Ultra Rare'): 
         {
            $itemJSON_Card['rarityIndex'] = 7;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }

         case strtolower('Illustration Rare'): 
         {
            $itemJSON_Card['rarityIndex'] = 8;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }

         case strtolower('Special Illustration Rare'): 
         {
            $itemJSON_Card['rarityIndex'] = 9;
            $itemJSON_Card['raritySimplified'] = 'ultra rare';
            break;
         }

         case strtolower('Rare Rainbow'):
         case strtolower('rare secret'):
         {
            $itemJSON_Card['rarityIndex'] = 10;
            $itemJSON_Card['raritySimplified'] = 'secret rare';
            break;
         }

         default:
         {
            return false;	
         }
      }

      return $itemJSON_Card;
   }
?>