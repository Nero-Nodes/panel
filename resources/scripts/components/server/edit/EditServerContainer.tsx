import editServer from '@/api/server/edit/editServer';
import Button from '@/components/elements/Button';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import FlashMessageRender from '@/components/FlashMessageRender';
import { megabytesToHuman } from '@/helpers';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from '@/state/hooks';
import { ServerContext } from '@/state/server';
import { faHdd, faTimesCircle, faPlusCircle, faMemory, faMicrochip } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import React from 'react';
import { Link } from 'react-router-dom';
import tw from 'twin.macro';

const EditServerContainer = () => {
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();

    const resources = useStoreState(state => state.user.data!);
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

    const addRAM = () => {
        clearFlashes('settings');

        editServer(uuid, 2, 1024)
            .then(() => addFlash({
                type: 'success',
                key: 'settings',
                message: '1GB RAM added to server.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'settings', error });
            });
    };

    const delRAM = () => {
        clearFlashes('settings');

        editServer(uuid, 2, -1024)
            .then(() => addFlash({
                type: 'success',
                key: 'settings',
                message: '1GB RAM removed from server.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'settings', error });
            });
    };

    const addDisk = () => {
        clearFlashes('settings');

        editServer(uuid, 3, 1024)
            .then(() => addFlash({
                type: 'success',
                key: 'settings',
                message: '1GB Storage added to server.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'settings', error });
            });
    };

    const delDisk = () => {
        clearFlashes('settings');

        editServer(uuid, 3, -1024)
            .then(() => addFlash({
                type: 'success',
                key: 'settings',
                message: '1GB Storage removed from server.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'settings', error });
            });
    };

    return (
        <>
            <FlashMessageRender byKey={'settings'} css={tw`mb-4`} />

            <TitledGreyBox title={'Edit Server'} css={tw`p-8`}>
                Edit your server with this easy-to-use utility. Resources can be added or taken away
                from your server. You must buy more resources at the <Link to={'/store'}>Store</Link>
                in order to add resources to your server.
            </TitledGreyBox>

            <div css={tw`flex justify-center items-center p-8`}>
                <TitledGreyBox
                    title={'Edit CPU Limit'}
                    icon={faMicrochip}
                    css={tw`flex-1 lg:flex-none lg:w-1/3`}
                >
                    {resources.crCpu}% available
                    <div css={tw`flex justify-center items-center`}>
                        <Button css={tw`mt-2 p-1`} onClick={addCPU}>
                            <FontAwesomeIcon icon={faPlusCircle} /> Add 50%
                        </Button>
                        <Button css={tw`mt-2 p-1`} onClick={delCPU}>
                            <FontAwesomeIcon icon={faTimesCircle} /> Remove 50%
                        </Button>
                    </div>
                </TitledGreyBox>
                <TitledGreyBox
                    title={'Edit RAM Limit'}
                    icon={faMemory}
                    css={tw`flex-1 lg:flex-none lg:w-1/3 ml-4`}
                >
                    {megabytesToHuman(resources.crRam)} available
                    <div css={tw`flex justify-center items-center`}>
                        <Button css={tw`mt-2 p-1`} onClick={addRAM}>
                            <FontAwesomeIcon icon={faPlusCircle} /> Add 1GB
                        </Button>
                        <Button css={tw`mt-2 p-1`} onClick={delRAM}>
                            <FontAwesomeIcon icon={faTimesCircle} /> Remove 1GB
                        </Button>
                    </div>
                </TitledGreyBox>
                <TitledGreyBox
                    title={'Edit Storage Limit'}
                    icon={faHdd}
                    css={tw`flex-1 lg:flex-none lg:w-1/3 ml-4`}
                >
                    {megabytesToHuman(resources.crStorage)} available
                    <div css={tw`flex justify-center items-center`}>
                        <Button css={tw`mt-2 p-1`} onClick={addDisk}>
                            <FontAwesomeIcon icon={faPlusCircle} /> Add 1GB
                        </Button>
                        <Button css={tw`mt-2 p-1`} onClick={delDisk}>
                            <FontAwesomeIcon icon={faTimesCircle} /> Remove 1GB
                        </Button>
                    </div>
                </TitledGreyBox>
            </div>
        </>
    );
};

export default EditServerContainer;
