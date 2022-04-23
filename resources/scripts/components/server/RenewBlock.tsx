import React, { useState } from 'react';
import tw from 'twin.macro';
import { faServer } from '@fortawesome/free-solid-svg-icons';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '../elements/Button';
import renewServer from '@/api/store/renewServer';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '../FlashMessageRender';
import { useStoreState } from 'easy-peasy';

const RenewBlock = ({ status }: { status: boolean }) => {
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();
    const [ isSubmit, setSubmit ] = useState(false);

    const uuid = ServerContext.useStoreState(state => state.server.data!.uuid);
    const current = ServerContext.useStoreState(state => state.server.data!.renewal);
    const renewal = useStoreState(state => state.settings.data!.store.cost.renewal);

    const submit = () => {
        clearFlashes('server:renewal');
        setSubmit(true);

        renewServer(uuid, current)
            .then(() => setSubmit(false))
            .then(() => addFlash({
                type: 'success',
                key: 'server:renewal',
                message: 'Server has been renewed for an extra 7 days.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'server:renewal', error });
                setSubmit(false);
            });
    };

    return (
        <>
            {status === true ?
                <>
                    <FlashMessageRender byKey={'server:renewal'} css={tw`mb-1`}/>
                    <TitledGreyBox css={tw`break-words`} title={'Renew Server'} icon={faServer}>
                        <p css={tw`text-sm mt-2`}>
                            Renewal Cost: {renewal} coins
                        </p>
                        <p css={tw`text-xs mt-2`}>
                            Renewing your server means that it will continue to run 24/7.
                            If you do not renew your server before the days left hits 0,
                            your server will be suspended. If you do not renew it within
                            a week of it being suspended, your server will be deleted and
                            the files will be purged. We strongly recommend you renew your
                            server to avoid any interruption in service.
                        </p>
                        <Button
                            onClick={submit}
                            disabled={isSubmit}
                            css={tw`mt-2`}
                        >
                            Renew (+7 days)
                        </Button>
                    </TitledGreyBox>
                </>
                :
                <TitledGreyBox css={tw`break-words`} title={'Exempt from renewals'} icon={faServer}>
                    <p css={tw`text-xs mt-2`}>
                        This server does not need to be renewed.
                    </p>
                </TitledGreyBox>
            }
        </>
    );
};

export default RenewBlock;
