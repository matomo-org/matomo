/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import './CapabilitiesEdit/CapabilitiesEdit.adapter';
import './UserPermissionsEdit/UserPermissionsEdit.adapter';
import './UserEditForm/UserEditForm.adapter';
import './PagedUsersList/PagedUsersList.adapter';
import './UsersManager/UsersManager.adapter';

export { default as CapabilitiesEdit } from './CapabilitiesEdit/CapabilitiesEdit.vue';
export { default as Capability } from './CapabilitiesStore/Capability';
export { default as UserPermissionsEdit } from './UserPermissionsEdit/UserPermissionsEdit.vue';
export { default as UserEditForm } from './UserEditForm/UserEditForm.vue';
export { default as PagedUsersList } from './PagedUsersList/PagedUsersList.vue';
export { default as UsersManager } from './UsersManager/UsersManager.vue';
