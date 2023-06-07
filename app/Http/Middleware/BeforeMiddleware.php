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
        $menuRole = MenuRole::where('role_id', $userRole)->first();

       // return $this->rejectError($request, "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);

        if (!$menuRole) {
            return $this->rejectError('no_access', "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);
            //throw new HttpException(403, 'You do not have access to this resource.');
        }

      
        $menuId = $menuRole->menu_id;
        $menuString = array_map('intval', explode(',', trim($menuId, '[]')));

        //Get the ID of the route 
        $getRouteID = intval(SubMenu::where("menu_url", $replacedUrl)->first()->menu_id);

        if (in_array($getRouteID, $menuString)) {
            
            return $next($request);
          // return $this->sendError('YES', "You don't have access", Response::HTTP_UNAUTHORIZED);
        }
        
        //$hasAccess = SubMenu::whereIn('menu_id', $menuString)->exists();

       // return $this->rejectError($getRouteID , $menuString, Response::HTTP_UNAUTHORIZED);
            
        // if($hasAccess){
            
        // }
        
        return $this->sendError('no_access', "You do not have access to this resource", Response::HTTP_UNAUTHORIZED);
        //throw new HttpException(403, 'You do not have access to this submenu.');

    }
}
