import { usePage } from '@inertiajs/vue3'

export function usePermissions() {
    const page = usePage()

    /**
     * 檢查使用者是否具有指定權限
     */
    const can = (permission) => {
        const permissions = page.props.auth?.permissions || []
        return permissions.includes(permission)
    }

    /**
     * 檢查使用者是否具有任一指定權限
     */
    const canAny = (permissions) => {
        const userPermissions = page.props.auth?.permissions || []
        return permissions.some(permission => userPermissions.includes(permission))
    }

    /**
     * 檢查使用者是否具有所有指定權限
     */
    const canAll = (permissions) => {
        const userPermissions = page.props.auth?.permissions || []
        return permissions.every(permission => userPermissions.includes(permission))
    }

    /**
     * 檢查使用者是否具有指定角色
     */
    const hasRole = (role) => {
        const roles = page.props.auth?.roles || []
        return roles.includes(role)
    }

    /**
     * 檢查使用者是否具有任一指定角色
     */
    const hasAnyRole = (roles) => {
        const userRoles = page.props.auth?.roles || []
        return roles.some(role => userRoles.includes(role))
    }

    return {
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole
    }
}