import editServer from '@/api/server/edit/editServer';
import Button from '@/components/elements/Button';
import Label from '@/components/elements/Label';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import { bytesToHuman } from '@/helpers';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from '@/state/hooks';
import { ServerContext } from '@/state/server';
import { faCross, faPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import React from 'react';
import tw from 'twin.macro';

const EditServerContainer = () => {
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();

    const resources = useStoreState(state => state.user.data!);
    const limits = ServerContext.useStoreState(state => state.server.data!.limits);
    const uuid = ServerContext.useStoreState(state => state.server.data!.uuid);

    /**
     * RESOURCES TO INTEGER
     * CPU => 1
     * RAM => 2
     * DISK = 3
     */
    const addCPU = () => {
        clearFlashes('settings');

        editServer(uuid, 1, 50)
            .then(() => console.log('Successfully added 50% CPU.'))
            .then(() => addFlash({
                type: 'success',
                key: 'settings',
                message: '50% CPU added to server.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'settings', error });
            });
    };

    const delCPU = () => {
        clearFlashes('settings');

        editServer(uuid, 1, -50)
            .then(() => console.log('Successfully removed 50% CPU.'))
            .then(() => addFlash({
                type: 'success',
                key: 'settings',
                message: '50% CPU removed from server.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'settings', error });
            });
    };

    return (
        <TitledGreyBox title={'Edit Server'}>
            Edit your server with this easy-to-use utility.
            <p>Free CPU: {resources.crCpu}</p>
            <p>Free RAM: {resources.crRam}</p>
            <p>Free Disk: {resources.crStorage}</p>
            Current server resources:
            <p>CPU: {limits.cpu}</p>
            <p>RAM: {bytesToHuman(limits.memory)}</p>
            <p>Disk: {bytesToHuman(limits.disk)}</p>
            <Label>Edit CPU amount</Label>
            <Button css={tw`mt-2`} onClick={addCPU}>
                <FontAwesomeIcon icon={faPlus}/>
            </Button>
            <Button css={tw`mt-2`} onClick={delCPU}>
                <FontAwesomeIcon icon={faCross} />
            </Button>
        </TitledGreyBox>
    );
};

export default EditServerContainer;
