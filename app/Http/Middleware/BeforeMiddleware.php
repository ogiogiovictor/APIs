<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuRole;
use App\Models\SubMenu;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Controllers\BaseApiController;
use Illuminate\Support\Facades\Route;
use App\Models\AssignSubMenu;



class BeforeMiddleware  extends BaseApiController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
       $requestUrl = $request->getRequestUri();
       $requestMethod = $request->getMethod();
       $replacedUrl = str_replace('/api/v1/', '', $requestUrl);

       
             
       $userRole = Auth::user()->roles->pluck('id')->first();
     //  return $this->rejectError('no_access',  $replacedUrl, Response::HTTP_UNAUTHORIZED);
       
     // Remove everything after the '?' in the menu_url
       $removeQuesionMarkURL = strtok($replacedUrl, '?');

       //return $this->rejectError('no_access', $removeQuesionMarkURL, Response::HTTP_UNAUTHORIZED);

        if (!$userRole) {
            return $this->rejectError('no_access', "You are not assigned a role", Response::HTTP_UNAUTHORIZED);
            //throw new HttpException(403, 'You do not have access to this resource.');
        }

       $getURL = SubMenu::where("menu_url", $removeQuesionMarkURL)->where("menu_status", "sub")->first();

       if($getURL){
        $checkforAccess = AssignSubMenu::where("role_id", $userRole)->where("sub_menu_id", $getURL->id)->first();

            if ($checkforAccess) {
                return $next($request);
            } else {
                return $this->sendError('no_access', "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);
            }
            
       }else {
        return $this->sendError('no_access', "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);
       }
      

      /* if($checkforAccess){
           return $next($request);
       }else {
        return $this->sendError('no_access', "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);
       }
       */



      
        //$menuId = $menuRole->menu_id;
       // $menuString = array_map('intval', explode(',', trim($menuId, '[]')));

        //Get the ID of the route 
      /*  if($requestMethod == 'GET'){

            //For Roles
            //$getRouteID = intval(SubMenu::where("menu_url", $replacedUrl)->where("menu_status", "sub")->first()->menu_id);

            $getRouteID = 0; // Default value if the query doesn't return a valid result

            $subMenu = SubMenu::where("menu_url", $replacedUrl)->where("menu_status", "sub")->first();
            if ($subMenu) {
                $getRouteID = intval($subMenu->menu_id);
            }

         
            if (in_array($getRouteID, $menuString)) {
                
                return $next($request);
            }

              //For Permission
            $getPermission = SubMenu::where("permission_id", [$userRole])->where("menu_status", "inner")->first()->permission_id;


            if($getPermission){
                return $next($request);
            }


        }
        */
     
        
        return $this->sendError('no_access', "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);
        //throw new HttpException(403, 'You do not have access to this submenu.');

    }
}
